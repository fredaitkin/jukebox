<?php

/**
 * Controller for artist requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Artist\ArtistInterface as Artist;
use App\Jukebox\Song\SongInterface as Song;
use App\Traits\StoreImageTrait;
use Illuminate\Http\Request;
use URL;

/**
 * ArtistRestController handles artist REST requests.
 *
 * Standard artist REST requests such as get, post, delete
 */
class ArtistRestController extends Controller
{

    use StoreImageTrait;

    /**
     * The artist interface
     *
     * @var App\Jukebox\Artist\SongInterface
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
     * @param App\Jukebox\Artist\ArtistInterface $artist The artist interface
     * @param App\Jukebox\Song\SongInterface     $song   The song interface
     */
    public function __construct(Artist $artist, Song $song)
    {
        $this->artist = $artist;
        $this->song = $song;
    }

    /**
     * Display artists
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $artists = $this->artist->all($request);
        if (empty($request->all()) || $request->has('page')):
            return view('artists', ['artists' => $artists]);
        endif;

        return $artists;
    }

    /**
     * Show the form for creating a new artist
     *
     * @return Response
     */
    public function create()
    {
        return view('artist', ['title' => 'Add Artist', 'countries' => config('countries')]);
    }

    /**
     * Store a newly created artist in the database
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->artist->createOrUpdate($request);
        return redirect('/artists');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id Song id
     *
     * @return Response
     */ 
    public function edit($id)
    {
        $artist = $this->artist->get($id ?? null);
        $albums = null;
        if ($artist->artist === 'Compilations'):
            $albums = array_merge(['' => 'Please Select...'], $this->artist->getArtistAlbums($id));
        endif;
        if (! empty($artist->photo)):
            if (strpos($artist->photo, 'cdn') === false):
                $artist->photo = URL::to('/') . '/storage/artists/' . $artist->photo;
            endif;
        endif;
        return view(
            'artist', [
                'title'     => $artist->artist,
                'artist'    => $artist,
                'albums'    => $albums,
                'songs'     => $this->song->getArtistSongs($id, $artist->artist),
                'countries' => array_merge(['' => 'Please Select...'], config('countries')),
            ]
        );
    }

    /**
     * Remove the artist and all their songs from the database
     *
     * @param Illuminate\Http\Request $request Request object
     * @param int                     $id      Song id
     *
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $this->artist->destroy($id);
    }

}
