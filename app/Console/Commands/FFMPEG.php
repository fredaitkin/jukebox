<?php

/**
 * FFMPEG.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Artist\ArtistInterface as Artist;
use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Use ffmpeg tools to:
 * - convert files to mp3 format
 * - get file information such as file size and composer
 */
class FFMPEG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffmpeg:convert
                            {--list : List songs in mp4 format}
                            {--song= : The song to convert}
                            {--artist= : The artist to convert}
                            {--all : All artists}
                            {--album-dir= : The album directory}
                            {--probe : Get song information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert apples songs to mp3 format';

    /**
     * The root directory.
     *
     * @var string
     */
    protected $root_dir;

    /**
     * The media directory.
     *
     * @var string
     */
    protected $media_dir;

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * The artist interface
     *
     * @var App\Jukebox\Artist\ArtistInterface
     */
    private $artist;

    /**
     * Create a new command instance.
     *
     * @param App\Jukebox\Artist\ArtistInterface $artist Artist interface
     * @param App\Jukebox\Song\SongInterface     $song   Song interface
     *
     * @return void
     */
    public function __construct(Artist $artist, Song $song)
    {
        parent::__construct();
        $this->artist = $artist;
        $this->song = $song;
        $this->root_dir = Config::get('filesystems.disks')[Config::get('filesystems.partition')]['root'];
        $this->media_dir = Config::get('filesystems.media_directory');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            $options = $this->options();

            // Do the ff tools exist?
            $return = shell_exec(sprintf("which %s", escapeshellarg('ffmpeg')));
            if (! $return):
                $this->error('The command ffmpeg does not exist or is not configured on your system');
                exit;
            endif;
            $return = shell_exec(sprintf("which %s", escapeshellarg('ffprobe')));
            if (! $return):
                $this->error('The command ffprobe does not exist or is not configured on your system');
                exit;
            endif;

            // Log a list of mp4 songs.
            if ($options['list']):
                $iter = new \GlobIterator($this->root_dir . $this->media_dir . '*/*/*.mp4');
                foreach ($iter as $file) {
                    Log::info($file);
                }
            endif;

            // Convert a song.
            if (isset($options['song'])):
                if (strpos($options['song'], '.mp4') !== false):
                    $new_file = str_replace('.mp4', '.mp3', $options['song']);
                    $command = 'ffmpeg -i "' . $options['song'] . '" "' . $new_file . '"';
                    exec($command);
                    $this->info('The song has been converted.');
                endif;
            endif;

            // Convert songs in an album.
            if (isset($options['album-dir'])):
                if (! is_dir($options['album-dir'])):
                    $this->error('Invalid diretory path');
                    exit;
                endif;

                foreach (scandir($options['album-dir']) as $file):
                    if ($file !== '.' && $file !== '..' && $file != '.DS_Store'):
                        if (strpos($file, '.mp4') !== false):
                            $this->info($file);
                            $new_file = str_replace('.mp4', '.mp3', $file);
                            $command = 'ffmpeg -i "' . $options['dir'] . '/' . $file . '" "' . $options['dir'] . '/' . $new_file . '"';
                            exec($command);
                        endif;
                    endif;
                endforeach;
                $this->info('The songs have been converted.');
            endif;

            // Convert songs by an artist.
            if (isset($options['artist'])):
                $this->processArtist($options['artist']);
            endif;

            // Convert songs by all artists.
            if ($options['all']):
                $this->processAllArtists();
            endif;

            // Get song information.
            if ($options['probe']):
                $this->getSongInformation();
            endif;

        } catch (Exception $e) {
            $this->error('The conversion process has been failed: ' . $e->getMessage());
        }
    }

    /**
     * Process artist directory
     *
     * Convert mp4 files, update the song filesize and location, and delete the
     * mp4 version.
     *
     * @param string $artist_name Artist name
     *
     * @return void
     */
    function processArtist($artist_name)
    {
        $artists = $this->artist->allByConstraints(['artist' => $artist_name]);
        if (count($artists) > 0):
            $songs = $artists[0]->songs;
            foreach($songs as $song):
                try {
                    if (strpos($song->location, 'mp4') !== false):
                        $song->location = str_replace("\\", DIRECTORY_SEPARATOR, $song->location);
                        $mp4_file = $this->root_dir . $this->media_dir . $song->location;
                        $mp3_file = str_replace('.mp4', '.mp3', $mp4_file);
                        $command = 'ffmpeg -i "' . $mp4_file . '" "' . $mp3_file . '"';
                        exec($command);
                        $command = 'ffprobe -v quiet -print_format json -show_format "' . $mp3_file . '"';
                        $details = shell_exec($command);
                        $details = json_decode($details);
                        $song->location = str_replace('.mp4', '.mp3', $song->location);
                        $song->filesize = $details->format->size;
                        $song->save();
                        File::delete($mp4_file);
                    endif;
                } catch (Exception $e) {
                    $this->info($command);
                    exit;
                }
            endforeach;
        endif;
    }

    /**
     * Process artists
     *
     * Convert mp4 files, update the song filesize and location, and delete the
     * mp4 version.
     *
     * @return void
     */
    function processAllArtists()
    {
        $artists = $this->artist->allByConstraints();
        foreach($artists as $artist):
            echo $artist->artist . ' ';
            $songs = $artist->songs;
            foreach($songs as $song):
                try {
                    if (strpos($song->location, 'mp4') !== false):
                        $song->location = str_replace("\\", DIRECTORY_SEPARATOR, $song->location);
                        $mp4_file = $this->root_dir . $this->media_dir . $song->location;
                        $mp3_file = str_replace('.mp4', '.mp3', $mp4_file);
                        $command = 'ffmpeg -i "' . $mp4_file . '" "' . $mp3_file . '" -report -loglevel error';
                        $success = exec($command);
                        $command = 'ffprobe -v quiet -print_format json -show_format "' . $mp3_file . '"';
                        $details = shell_exec($command);
                        $details = json_decode($details);
                        $song->location = str_replace('.mp4', '.mp3', $song->location);
                        $song->filesize = $details->format->size;
                        $song->save();
                        File::delete($mp4_file);
                    endif;
                } catch (Exception $e) {
                    Log::info($artist->artist);
                    Log::info($song->location);
                    Log::info($command);
                    Log::info($e->getMessage());
                }
            endforeach;
        endforeach;
    }

    /**
     * Get song information
     *
     * @return void
     */
    function getSongInformation()
    {
        $songs = $this->song->allByConstraints(['composer_empty' => true]);
        foreach($songs as $song):
            try {
                $song->location = str_replace("\\", DIRECTORY_SEPARATOR, $song->location);
                $file = $this->root_dir . $this->media_dir . $song->location;
                $command = 'ffprobe -v quiet -print_format json -show_format "' . $file . '"';
                $details = shell_exec($command);
                $details = json_decode($details);
                if (isset($details->format->tags->composer)):
                    $composer = str_replace(', ', '/', $details->format->tags->composer);
                    Log::info('COMPOSER:' . $details->format->tags->composer);
                    if ($composer != $details->format->tags->composer):
                        Log::info('COMPOSER:' . $composer);
                    endif;
                    // $song->filesize = $details->format->size;
                    // $song->save();
                endif;
            } catch (Exception $e) {
                Log::info($song->location);
                Log::info($command);
                Log::info($e->getMessage());
            }
        endforeach;
    }
}
