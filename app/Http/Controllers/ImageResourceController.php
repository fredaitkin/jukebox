<?php

/**
 * Controller to handle image requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jukebox\Song\SongInterface as Song;
use Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * ImageResourceController handles image requests.
 *
 * Get song cover art from storage.
 */
class ImageResourceController extends Controller
{

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * Constructor
     *
     * @param App\Jukebox\Song\SongInterface $song The song object
     */
    public function __construct(Song $song)
    {
        $this->song = $song;
    }

    /**
     * Retrieve song cover art.
     *
     * @param int $id The song id
     *
     * @return Response
     */
    public function coverArt($id)
    {
        if (! Cache::has('song_photo_' . $id)) :
            $song = $this->song->get($id);
            $location = explode('/', $song->location);
            // Handle MAC and Win directory structures.
            if (count($location) < 2) :
                $location = explode('\\', $song->location);
            endif;
            $location = config('filesystems.media_directory') . $location[0] . DIRECTORY_SEPARATOR . $location[1];
            $files = Storage::disk(config('filesystems.partition'))->files($location);
            $path = null;
            if (count($files) > 0) :
                foreach ($files as $file):
                    if (strpos($file, 'Large.jpg')) :
                        $path = config('filesystems.disks')[config('filesystems.partition')]['root'] . $file;
                        Cache::put('song_photo_' . $id, $path, 86400);
                        break;
                    endif;
                endforeach;
            endif;
        else:
            $path = Cache::get('song_photo_' . $id);
        endif;

        if (! $path) :
            $path = Storage::disk('public')->path('black.jpeg');
        endif;
        return Response::download($path);
    }
}
