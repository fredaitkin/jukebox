<?php

/**
 * UpdateLyrics.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Dictionary\WordCloudInterface as WordCloud;
use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Use the api.chartlyrics API to update song lyrics via the command line
 */
class UpdateLyrics extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:lyrics
                            {--id= : Song id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs song utilities';

    /**
     * Chart Lyrics API URL
     *
     * @var string
     */
    protected $url = "http://api.chartlyrics.com/apiv1.asmx/";

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * The wordCloud interface
     *
     * @var App\Jukebox\Dictionary\WordCloudInterface
     */
    private $wordCloud;

    /**
     * Constructor
     *
     * @param App\Jukebox\Song\SongInterface            $song      Song interface
     * @param App\Jukebox\Dictionary\WordCloudInterface $wordCloud WordCloud interface
     */
    public function __construct(Song $song, WordCloud $wordCloud)
    {
        parent::__construct();
        $this->song = $song;
        $this->wordCloud = $wordCloud;
        libxml_use_internal_errors(true);
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();

        if(! empty($options['id'])):
            $this->updateLyrics($options['id']);
        else:
            $this->error('Song ID is required');
            exit;
        endif;

    }

    /**
     * Update song lyrics.
     *
     * @param int $id Song id
     *
     * @return void
     */
    protected function updateLyrics($id)
    {
        $artists_names = [
            'Dinosaur Junior'           => 'Dinosaur Jr.',
            'Florence + the Machine'    => 'Florence and The Machine',
            'Run-D.M.C'                 => 'Run-D.M.C.',
            'Pink'                      => 'P!nk',
        ];
        $song = $this->song->get($id);

        if (isset($song->id)):

            $artist = $song->artists[0]->artist;
            if (isset($artists_names[$artist])):
                $artist = $artists_names[$artist];
            endif;

            try {
                if ($artist == 'Compilations'):
                    $artist = trim($song->notes);
                endif;
                $this->info("ARTIST: " . $artist);
                $this->info("TITLE: " . $song->title);
                $this->info("ID: " . $song->id);
                $lyric = $this->directSearch($artist, $song->title);
                if (! $lyric):
                    $lyric = $this->search($artist, $song->title);
                endif;

                if (! empty($lyric)):
                    $this->info($lyric);
                    $this->info("");
                    $this->info($artist . ' : ' . $song->title);
                    $save = $this->ask('Save the lyrics? (y/n)');
                    if ($save === 'y'):
                        $lyric = $this->wordCloud->process($lyric, 'add', $song->id);
                        $song->lyrics = $lyric;
                        $song->save();
                    else:
                        $this->info("Ignoring");
                    endif;
                endif;

            } catch (Exception $e) {

                $this->error($song->title . ' ' . $artist);
                $this->error($e->getMessage());
            }

             $this->info("ID: " . $song->id);

        endif;

    }

    /**
     * Perform direct search via API.
     *
     * @param string $artist Song artist
     * @param string $song   Song title
     *
     * @return mixed
     */
    private function directSearch($artist, $song)
    {
        $this->info("Direct Search");

        $response = Http::get($this->url . "SearchLyricDirect?artist=" . urlencode($artist) . "&song=" . urlencode($song));
        $this->info($this->url . "SearchLyricDirect?artist=" . urlencode($artist) . "&song=" . urlencode($song));
        $xml = simplexml_load_string($response);

        if (false === $xml):
            return false;
        endif;

        if (isset($xml) && ! empty($xml->Lyric)):
            $this->info("LYRIC ARTIST: " . $xml->LyricArtist);
            $this->info("LYRIC SONG: " . $xml->LyricSong);
            if (strcasecmp($artist, (string) $xml->LyricArtist) === 0 && strcasecmp($song, (string) $xml->LyricSong) === 0):
                return (string) $xml->Lyric;
            endif;
        endif;

        return false;
    }

    /**
     * Perform broader search via API.
     *
     * @param string $artist Song artist
     * @param string $song   Song title
     *
     * @return mixed
     */
    private function search($artist, $song)
    {
        $this->info("Wide Search");
        $response = Http::get($this->url . "SearchLyric?artist=" . urlencode($artist) . "&song=" . urlencode($song));
        $xml = simplexml_load_string($response);

        if (false === $xml):
            return false;
        endif;

        if (isset($xml->SearchLyricResult) && ! empty($xml->SearchLyricResult)):
            foreach ($xml->SearchLyricResult as $result):
                $this->info("Result Artist: " . $result->Artist);
                $this->info("Result Song: " . $result->Song);
                if (strcasecmp($artist, $result->Artist) === 0):
                    if (strcasecmp($song, $result->Song) === 0):
                        $this->info("Getting lyrics");
                        $this->info("Lyric ID: " . $result->LyricId);
                        $this->info("LyricChecksum: " . $result->LyricChecksum);
                        if ($result->LyricId > 0 && ! empty($result->LyricChecksum)):
                            return $this->getLyric($result->LyricId, $result->LyricChecksum);
                        endif;
                    endif;
                endif;
            endforeach;
        endif;

        return false;
    }

    /**
     * Retrieve lyric by id via API.
     *
     * @param string $id       Chart Lyric Id
     * @param string $checksum Checksum
     *
     * @return mixed
     */
    private function getLyric($id, $checksum)
    {
        $response = Http::get($this->url . "GetLyric?lyricId=" . $id . "&lyricCheckSum=" . $checksum);
        if (strpos($response, '<') === 0):
            $xml = simplexml_load_string($response);
            return (string) $xml->Lyric;
        else:
            $this->info($response);
        endif;

        return false;
    }

}
