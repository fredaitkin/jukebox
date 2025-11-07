<?php

/**
 * MusicAPI.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Artist\ArtistInterface as Artist;
use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Use the Deezer API to get song information via the command line
 */
class MusicAPI extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:music
                            {--years : Get song years}
                            {--photos : Get song photos}
                            {--ids= : Comma separated list of song ids}
                            {--id= : Artist id}
                            {--track= : Track}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs music api jobs';

    /**
     * X RAPID API KEY
     *
     * @var string
     */
    protected $x_rapid_api_key;

    /**
     * Deezer API HOST
     *
     * @var string
     */
    protected $deezer_host;

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
     * @param App\Jukebox\Song\SongInterface     $song   Song interface
     * @param App\Jukebox\Artist\ArtistInterface $artist Artist interface
     */
    public function __construct(Song $song, Artist $artist)
    {
        $this->x_rapid_api_key = config('app.x_rapid_api_key');
        $this->deezer_host = config('app.deezer_host');
        $this->song = $song;
        $this->artist = $artist;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();

        $ids = null;
        if(! empty($options['ids'])):
            $ids = explode(',', $options['ids']);
        endif;

        if(! empty($options['years'])):
            $this->updateSongYear($ids);
        endif;

        if(! empty($options['photos'])):
            if(! empty($options['id'])):
                $id = $options['id'];
                $track = $options['track'];
                $this->getPhotoForArtist($id, $track);
            else:
                $this->updatePhotos();
            endif;
        endif;
    }

    /**
     * Update song year.
     *
     * @param string $ids Song ids
     *
     * @return void
     */
    protected function updateSongYear($ids)
    {
        $constraints = ['year' => 9999];
        if ($ids):
            $constraints[0]['ids'] = $ids;
        endif;
        $songs = $this->song->allByConstraints($constraints);

        foreach ($songs as $song):
            $this->info($song->id . ':' . $song->title);
            try {
                $track = $this->search($song->title, $song->artists[0]->artist);
                if ($track):
                    $track_info = $this->track($track->id);
                    if ($track_info):
                        if (isset($track_info->release_date) && isset($track_info->album->release_date)):
                            if (strlen($track_info->release_date) == 10 && strlen($track_info->album->release_date) == 10):
                                $year = substr($track_info->release_date, 0, 4);
                                $album_year = substr($track_info->album->release_date, 0, 4);
                                $diff = abs($year - $album_year);
                                if ($diff > 2):
                                    $this->info('Disparity in release dates: ' . $year . ' ' . $album_year);
                                else:
                                    $this->info('Updating year to : ' . $year);
                                    $song->year = $year;
                                    $song->save();
                                endif;
                            endif;
                        else:
                            $this->info('Issues with dates');
                            $this->info($track_info);
                        endif;
                    else:
                        $this->info('Not found');
                    endif;
                endif;
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        endforeach;

    }

    /**
     * Update artists photos and genres.
     *
     * @return void
     */
    protected function updatePhotos()
    {
        $constraints = ['photo_empty' => true];
        $artists = $this->artist->allByConstraints($constraints);

        foreach ($artists as $artist):
            $this->info(" " . strtoupper($artist['artist']));
            foreach ($artist->songs as $song):
                try {
                    $artist_name = $artist['artist'];
                    if ($this->artistNotFound($artist_name)):
                        continue;
                    endif;
                    $this->info($artist_name . ':' . $song->title);
                    $track = $this->search($song->title, $artist_name);
                    if ($track):
                        $this->info("Track");
                        $this->info(print_r($track, true));
                        $photo = $track->artist->picture_big ?? '';
                        $album_info = $this->album($track->album->id);
                        $this->info("Album");
                        $this->info(print_r($album_info, true));
                        $genres = [];
                        if (isset($album_info->genres->data)):
                            foreach($album_info->genres->data as $genre):
                                $genres[] = $genre->name;
                            endforeach;
                        endif;
                        if (! empty($photo) || ! empty($genres)):
                            $artist->photo = $photo;
                            $artist->genres = implode(',', $genres);
                            $artist->save();
                            continue 2;
                        endif;
                    endif;
                } catch (Exception $e) {
                    $this->info($e->getMessage());
                }
            endforeach;
        endforeach;
    }

    /**
     * Update artist photos and genres.
     *
     * @param int    $id    Artist id
     * @param string $title Song title
     *
     * @return void
     */
    protected function getPhotoForArtist($id, $title)
    {
        try {
            $artist = $this->artist->get($id);
            if (isset($artist->artist)):
                $track = $this->search($title, $artist->artist);
                if ($track):
                    $this->info("Track");
                    $this->info(print_r($track, true));
                    $photo = $track->artist->picture_big ?? '';
                    $album_info = $this->album($track->album->id);
                    $this->info("Album");
                    $this->info(print_r($album_info, true));
                    $genres = [];
                    if (isset($album_info->genres->data)):
                        foreach($album_info->genres->data as $genre):
                            $genres[] = $genre->name;
                        endforeach;
                    endif;
                    if (! empty($photo) || ! empty($genres)):
                        $artist->photo = $photo;
                        $artist->genres = implode(',', $genres);
                    endif;
                endif;
            endif;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Artist does not exist in Deezer
     *
     * Previous runs have shown that the following artists do not exist in
     * Deezer.
     *
     * @param string $artist Artist
     *
     * @return bool
     */
    private function artistNotFound($artist)
    {
        return (in_array(
            $artist, [
            'Compilations',
            'G.I.T',
            'Gabriel Meyers',
            'Harem Turkish Percussion Group',
            'Hot Box',
            'Ministry Of Sound',
            'West Side Story',
            'Sri Chinmoy',
            'Uncle Bill',
            'Unknown Artist',
            ]
        ));
    }

    /**
     * Make get request
     *
     * @param string $url API request url
     *
     * @return mixed
     */
    private function getRequest($url)
    {
        $response = Http::withHeaders(
            [
            "X-RapidAPI-Key" => $this->x_rapid_api_key,
            "X-RapidAPI-Host" =>  $this->deezer_host,
            ]
        )->get($url);
        if ($response->getStatusCode() == 200):
            return json_decode($response->getBody());
        endif;
        return false;
    }

    /**
     * Perform broad search via API.
     *
     * @param string $title  Song title
     * @param string $artist Song artist
     *
     * @return bool|array
     */
    private function search($title, $artist)
    {
        $body = $this->getRequest('https://' . $this->deezer_host . '/search' . "?q=" . urlencode($title));
        if ($body):
            foreach($body->data as $track):
                if ($track->artist->name == $artist):
                    return $track;
                endif;
            endforeach;
        endif;
        return false;
    }

    /**
     * Get track info via API.
     *
     * @param int $id Track id
     *
     * @return bool|array
     */
    private function track($id)
    {
        return $this->getRequest('https://' . $this->deezer_host . '/track/' . $id);
    }

    /**
     * Get album info via API.
     *
     * @param int $id Track id
     *
     * @return bool|array
     */
    private function album($id)
    {
        return $this->getRequest('https://' . $this->deezer_host . '/album/' . $id);
    }

    /**
     * Get artist info via API.
     *
     * @param int $id Track id
     *
     * @return bool|array
     */
    private function artist($id)
    {
        return $this->getRequest('https://' . $this->deezer_host . '/artist/' . $id);
    }

}
