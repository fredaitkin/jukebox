<?php

/**
 * Controller for song requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Song\SongInterface as Song;
use Illuminate\Http\Request;
use Storage;

/**
 * SongResourceController handles song requests.
 *
 * Handles song requests such as add, search, play
 */
class SongResourceController extends Controller
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
     * @param App\Jukebox\Song\SongInterface $song Song interface
     */
    public function __construct(Song $song)
    {
        $this->song = $song;
    }

    /**
     * Show the form for creating a new song
     *
     * @return Response
     */
    public function add()
    {
        return view(
            'song', [
                'title'         => 'Add New Song',
                'file_types'    => array_merge(['' => 'Please Select...'], config('audio_file_formats')),
                'song_exists'   => false,
                'cover_art'     => false,
            ]
        );
    }

    /**
     * Search for song.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function search(Request $request)
    {
        $q = $request->q;

        if ($q != ""):
            session()->put('songs_query', $q);
        endif;
        
        $data = $this->song->search(session()->get('songs_query'));
        if ($data):
            // Data object can be a null, a view or a paginator
            if (get_class($data) === 'Illuminate\View\View'):
                return $data;
            else:
                if ($data->total() > 0):
                    return view('songs', ['q' => $q, 'songs' => $data]);
                else:
                    return view('songs', ['q' => $q, 'songs' => $data])->withMessage('No songs found. Try to search again!');
                endif;
            endif;
        else:
            return view('songs')->withMessage('No songs found. Try to search again!');
        endif;
    }

    /**
     * Play a song.
     *
     * @param int $id Song id
     *
     * @return Response
     */
    public function play($id)
    {
        $song = $this->song->get($id);
        $location = config('filesystems.media_directory') . $song->location;
        if (Storage::disk(config('filesystems.partition'))->fileExists($location)):
            $song->last_played = date("Y-m-d");
            $song->played += 1;
            $song->update();
            $contents = Storage::disk(config('filesystems.partition'))->get($location);
            $response = response($contents, 200)
                ->header("Content-Type", 'audio/mpeg')
                ->header("Content-transfer-encoding", 'binary')
                ->header("Accept-Ranges", "bytes");
            if ($song->filesize):
                $response->header("Content-Length", $song->filesize);
            endif;
            return $response;
        endif;
        return false;
    }

}
