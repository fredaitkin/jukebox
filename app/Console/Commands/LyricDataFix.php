<?php

/**
 * LyricDataFix.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;

/**
 * Tools to clean up lyrics via the command line
 */
class LyricDataFix extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:lyric
                            {--df : Data fix}
                            {--s= : String to find}
                            {--r= : String to replace}
                            {--id= : Song to find}
                            {--c : Clean lyric}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs lyric analysis and cleanup';

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * Create a new command instance.
     *
     * @param App\Jukebox\Song\SongInterface $song Song interface
     */
    public function __construct(Song $song)
    {
        parent::__construct();
        $this->song = $song;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();
        if(! empty($options['df'])):
            if(! empty($options['r'])):
                $this->replaceLyric($options['s'], $options['r'], $options['c'], $options['id']);
            else:
                $this->cleanLyric($options['s'], $options['c'], $options['id']);
            endif;
        endif;
    }

    /**
     * Find and clean lyrics
     *
     * @param string $str   String to remove
     * @param bool   $clean Whether to remove the string from the lyric.
     * @param int    $id    Specific song.
     *
     * @return void
     */
    protected function cleanLyric($str, $clean, $id = null)
    {
        $songs = [];
        if ($id):
            $songs[] = $this->song->get($id);
        else:
            $songs = $this->song->getSongsByLyric($str)->toArray();
        endif;

        foreach ($songs as $song):
            try {
                $lyric = str_replace($str, '', $song['lyrics']);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            $this->info($lyric ?? 'No match');

            if ($clean):
                $song->lyrics = $lyric;
                $song->save();
                $this->info("Song {$song['id']} was updated");
            endif;
        endforeach;
    }

    /**
     * Replace words in lyrics
     *
     * @param string $str   String to replace
     * @param string $repl  Replacement string
     * @param bool   $clean Whether to replace the string in the lyric.
     * @param int    $id    Specific song.
     *
     * @return void
     */
    protected function replaceLyric($str, $repl, $clean, $id)
    {
        if ($id):
            $query = $this->song->get($id);
            $song = $query->first()->toArray();

            $lyric = $song['lyrics'];
            $lyric = str_replace($str, $repl, $lyric);

            $this->info($lyric);

            if ($clean):
                $song->lyrics = $lyric;
                $song->save();
                $this->info("Song {$song['id']} was updated");
            endif;
        endif;
    }

}
