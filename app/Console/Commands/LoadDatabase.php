<?php

/**
 * LoadDatabase.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;
use Log;

/**
 * Load the Jukebox database via the command line
 */
class LoadDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load database from backup';

    /**
     * Create a new command instance.
     *
     * @param App\Jukebox\Song\SongInterface $song Song interface
     */
    public function __construct(Song $song)
    {
        $this->song = $song;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            // Retain song stats for the device.
            $stats = $this->song->allByConstraints([], ['id', 'played', 'last_played']);

            $command = sprintf(
                'mysql -u%s -p%s --port=%s %s < %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.port'),
                config('database.connections.mysql.database'),
                storage_path('backups/mymusic.sql')
            );

            exec($command);

            $this->updateSongStats($stats);

            $this->info('The database load has been processed successfully.');
        } catch (Exception $exception) {
            Log::info($exception);
            $this->error('The database load process has been failed.');
        }
    }

    /**
     * Update song stats
     *
     * @return void
     */
    protected function updateSongStats($stats)
    {
        foreach ($stats as $stat):
            if ($stat->played):
                $song = $this->song->get($stat->id);
                $song->played = $stat->played;
                $song->last_played = $stat->last_played;
                $song->save();
            endif;
        endforeach;
    }
}
