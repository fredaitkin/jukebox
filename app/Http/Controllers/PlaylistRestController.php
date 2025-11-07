<?php

/**
 * Controller for playlist requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Playlist\PlaylistInterface as Playlist;
use Illuminate\Http\Request;

/**
 * PlaylistRestController handles playlist REST requests.
 *
 * Standard playlist REST requests such as get, post, delete
 */
class PlaylistRestController extends Controller
{

    /**
     * The playlist interface
     *
     * @var App\Jukebox\Playlist\PlaylistInterface
     */
    private $playlist;

    /**
     * Constructor
     *
     * @param App\Jukebox\Playlist\PlaylistInterface $playlist Playlist interface
     */
    public function __construct(Playlist $playlist)
    {
        $this->playlist = $playlist;
    }

    /**
     * Display playlists
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $playlists = $this->playlist->all($request);
        if (empty($request->all()) || $request->has('page')):
            return view('playlists', ['playlists' => $playlists]);
        endif;
        return $playlists;
    }

    /**
     * Add songs to a playlist
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->playlist->createOrUpdate($request);
    }

    /**
     * Edit playlist
     *
     * @param Illuminate\Http\Request $request Request object
     * @param string                  $name    Playlist list name
     *
     * @return mixed
     */
    public function edit(Request $request, $name)
    {
        $playlist = $this->playlist->allByConstraints(['playlist' => $name]);
        return view(
            'playlist', [
                'playlist' => $name,
                'songs' => (array) json_decode($playlist[0]->playlist),
            ]
        );
    }

    /**
     * Update playlist
     *
     * @param Illuminate\Http\Request $request Request object
     * @param string                  $name    Playlist name
     * @param int                     $id      Song id
     *
     * @return mixed
     */
    public function update(Request $request, $name, $id)
    {
        $playlist = $this->playlist->allByConstraints(['playlist' => $name]);
        $playlist = $playlist[0];
        $songs = (array) json_decode($playlist->playlist);
        foreach($songs as $idx => $song):
            if ($song->id === $id):
                unset($songs[$idx]);
            endif;
        endforeach;
        $playlist->playlist = json_encode($songs);
        $playlist->save();
        return redirect()->route('playlists.edit', $name);
    }

    /**
     * Remove the playlist
     *
     * @param Illuminate\Http\Request $request  Request object
     * @param string                  $playlist Playlist name
     *
     * @return Response
     */
    public function destroy(Request $request, $playlist)
    {
        $this->playlist->delete($playlist);
        return view('playlists', ['playlists' => $this->playlist->all($request)]);
    }

}
