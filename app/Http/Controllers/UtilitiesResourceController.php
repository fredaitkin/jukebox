<?php

/**
 * Controller for utility requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jukebox\Artist\ArtistInterface as Artist;
use App\Jukebox\AudioFile\AudioFile;
use App\Jukebox\AudioFile\MP3;
use App\Jukebox\AudioFile\MP4;
use App\Jukebox\Song\SongInterface as Song;
use Exception;
use getID3;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Storage;

/**
 * UtilitiesResourceController handles utility requests.
 *
 * Handles utility requests such as load songs, process artist directory.
 */
class UtilitiesResourceController extends Controller
{

    private $ID3_extractor;

    /**
     * The file system root
     *
     * @var string
     */
    private $partition_root;

    /**
     * Artifacts loaded
     *
     * @var int
     */
    private $count;

    /**
     * The artist interface
     *
     * @var App\Jukebox\Artist\ArtistInterface
     */
    private $artist;

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * Constructor
     *
     * @param App\Jukebox\Artist\ArtistInterface $artist Artist interface
     * @param App\Jukebox\Song\SongInterface     $song   Song interface
     */
    public function __construct(Artist $artist, Song $song)
    {
        $this->ID3_extractor = new getID3;
        $this->media_directory = config('filesystems.media_directory');
        $this->partition_root = config('filesystems.disks')[config('filesystems.partition')]['root'];
        $this->artist = $artist;
        $this->song = $song;
    }

    /**
     * Load songs
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function loadSongs(Request $request)
    {
        try {
            $this->count = 0;
            // Check media library has been set
            if(empty($this->media_directory)):
                return view('utilities')->withErrors(["The media library needs to be set at <a href='/settings'>Settings</a>"]);
            endif;
            // Check directories have been set
            if(empty($request->artist_directory) && empty($request->random_directory)):
                return view('utilities')->withErrors(["Please choose a directory"]);
            endif;
            if(! empty($request->artist_directory)):
                if(Storage::disk(config('filesystems.partition'))->has($this->media_directory . $request->artist_directory)):
                    if(isset($request->entire_library)):
                        $this->processMediaDirectory();
                    else:
                        $dirs = explode('\\', $request->artist_directory);
                        $artist_id = $this->processArtist($dirs[count($dirs)-1]);
                        $this->processArtistDirectory($request->artist_directory, $artist_id);

                    endif;
                else:
                    return Redirect::route('utilities.utilities')
                        ->with(['artist_directory' => $request->artist_directory])
                        ->withErrors(['The artist directory not a valid directory']);
                endif;
            endif;
            // Processing songs inside a random directory
            if(! empty($request->random_directory)):
                if(is_dir($request->random_directory)):
                    $scan_songs = glob($request->random_directory . '/*');
                    foreach($scan_songs as $song):
                        $this->processSongAndArtist($song);
                    endforeach;
                else:
                    return Redirect::route('utilities.utilities')
                        ->with(['random_directory' => $request->random_directory])
                        ->withErrors(['The random directory not a valid directory']);
                endif;
            endif;

        } catch (Exception $e) {
            return view('utilities')->withErrors([$e->getMessage()]);
        }
        return Redirect::route('utilities.utilities')
            ->with(
                [
                'msg' => $this->count . ' songs have been loaded',
                'random_directory' => $request->random_directory,
                'artist_directory' => $request->artist_directory,
                ]
            );
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @return Result
     */
    private function processMediaDirectory()
    {
        $result = [];
        $scan_items = glob($this->media_directory . '/*');
        foreach($scan_items as $item):
            if(is_dir($item)):
                $artist_id = $this->processArtist($item);
                $this->processArtistDirectory($item, $artist_id);
            else:
                if ($this->song->isSong($item)):
                    $this->processSong($artist_id, $item);
                endif;
            endif;
        endforeach;
        return $result;
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @param string $artist_dir Artist directory
     * @param int    $artist_id  Artist id
     *
     * @return void
     */
    private function processArtistDirectory(string $artist_dir, int $artist_id)
    {
        $scan_albums = glob($this->partition_root . $this->media_directory . $artist_dir . '/*');
        foreach($scan_albums as $album):
            if(is_dir($album)):
                $this->processAlbum($artist_dir, $album, $artist_id);
            else:
                if($this->song->isSong($album)):
                    $this->processSong($artist_id, $album);
                endif;
            endif;
        endforeach;
    }

    /**
     * Process artist
     *
     * @param string $item Artist name
     *
     * @return int
     */
    private function processArtist(string $item)
    {
        $artist_arr = [basename($item), 1, 'To Set'];
        $artist_id = $this->artist->getID($artist_arr[0]);
        if(! $artist_id):
            $artist_id = $this->artist->dynamicStore($artist_arr);
        endif;
        return $artist_id;
    }

    /**
     * Process album
     *
     * @param string $artist    Artist name
     * @param string $album     Artist album
     * @param int    $artist_id Artist id
     *
     * @return int
     */
    private function processAlbum(string $artist, string $album, int $artist_id)
    {
        $album_name = basename($album);
        if(preg_match('/[\[\]]/', $album_name)):
            throw new Exception("Album directory contains square brackets");
        endif;
        $album_exists = $this->song->doesAlbumExist($artist_id, $album_name);
        if(! $album_exists):
            $is_compilation = $this->artist->isCompilation($artist_id);
            $scan_songs = glob($album . '/*');
            foreach($scan_songs as $song):
                if($this->song->isSong($song)):
                    $song_info = $this->retrieveSongInfo($song, basename($song), $is_compilation);
                    $location = $artist . DIRECTORY_SEPARATOR . $album_name . DIRECTORY_SEPARATOR . basename($song);
                    $this->song->dynamicStore($location, $album_name, $artist_id, $song_info);
                    $this->count++;
                    // If the song is in a compilation but the artist does not exist, add the artist.
                    if($song_info->isCompilation()):
                        if(! empty($song_info->notes())):
                            if(! $this->artist->getID($song_info->notes())):
                                $this->artist->dynamicStore([$song_info->notes(), 1, 'To Set']);
                            endif;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
    }

    /**
     * Process song
     *
     * @param int    $artist_id Artist id
     * @param string $song      Song title
     *
     * @return void
     */
    private function processSong(int $artist_id, string $song)
    {
        $song_exists = $this->song->doesSongExist($artist_id, $song);
        if(! $song_exists):
            $is_compilation = $this->artist->isCompilation($artist_id);
            $song_info = $this->retrieveSongInfo($song, basename($song), $is_compilation);
            $this->song->dynamicStore($song, 'To Set', $artist_id, $song_info);
            $this->count++;
        endif;
    }

    /**
     * Add artists and songs to the database from a temporary directory and
     * move the songs into the media library
     *
     * @param String $song Song name including path
     *
     * @return void
     */
    private function processSongAndArtist(string $song)
    {
        try {
            Log::info("Processing " . $song);
            $song_info = $this->retrieveSongInfo($song, basename($song), false);

            Log::info("Artist " . $song_info->artist());

            if(empty($song_info->artist())):
                throw new Exception("Error processing " . $song . ": unknown artist");
            endif;

            $artist_id = $this->artist->getID($song_info->artist());

            // Process artist
            if(! $artist_id):
                // Create artist in database.
                $artist_id = $this->artist->dynamicStore([$song_info->artist(), 1, 'To Set']);
            endif;

            // Make artist folder
            // Artist might exist in a compilation, so also check for a physical folder.
            if(! Storage::disk(config('filesystems.partition'))->exists($this->media_directory . $song_info->artist())):
                Log::info("Making artist directory");
                // Create artist folder in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist());
            endif;

            // Make album folder
            if(! Storage::disk(config('filesystems.partition'))->exists($this->media_directory . $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album())):
                Log::info("Making album directory " . $song_info->album());
                // Create album folder under artist in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album());
            endif;

            // Process song
            if(! $this->song->doesSongExist($artist_id, $song_info->title())):
                Log::info("Adding and moving song " . $song_info->title());
                $new_song_location = $song_info->artist() . "\\" . $song_info->album() . DIRECTORY_SEPARATOR . $song_info->title() . "." . $song_info->fileType();
                // Create song in database
                $this->song->dynamicStore($new_song_location, $song_info->album(), $artist_id, $song_info);
                Storage::disk(config('filesystems.partition'))->move(str_replace($this->partition_root, '', $song), $this->media_directory . $new_song_location);
            else:
                // Song already exists, delete this one
                Log::info("Deleting existing song");
                Storage::disk(config('filesystems.partition'))->delete(str_replace($this->partition_root, '', $song));
            endif;
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Retrieve song info via ID3
     *
     * @param string  $path           Full file path
     * @param string  $filename       Filename
     * @param boolean $is_compilation Is song part of a compilation ablum
     *
     * @return array
     */
    private function retrieveSongInfo($path, $filename, $is_compilation)
    {
        $file_info = $this->ID3_extractor->analyze($path);

        if(isset($file_info['error'])):
            throw new Exception("Error processing " . $path . ": " . $file_info['error'][0]);
        endif;

        if($file_info['fileformat'] === 'quicktime'):
            throw new Exception("Error processing " . $path . ": incompatible file type");
        endif;

        switch($file_info['fileformat']):
        case "mp3":
            $song = new MP3($path, $filename, $is_compilation, $file_info);
            break;
        case "mp4":
            $song = new MP4($path, $filename, $is_compilation, $file_info);
            break;
        default:
            $song = new AudioFile($path, $filename, $is_compilation, $file_info, $file_info['fileformat']);
            break;
        endswitch;
        return $song;
    }

}
