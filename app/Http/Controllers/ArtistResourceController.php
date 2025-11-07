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
use Illuminate\Http\Request;

/**
 * ArtistResourceController handles artist requests.
 *
 * Various artist requests such as add artist, search for an artist.
 */
class ArtistResourceController extends Controller
{

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
     * @param App\Jukebox\Artist\ArtistInterface $artist The artist interface
     * @param App\Jukebox\Song\SongInterface     $song   The song interface
     */
    public function __construct(Artist $artist, Song $song)
    {
        $this->artist = $artist;
        $this->song = $song;
    }

    /**
     * Show the form for creating a new artist
     *
     * @return Response
     */
    public function add()
    {
        return view(
            'artist', [
                'title'     => 'Add New Artist',
                'countries' => array_merge(['' => 'Please Select...'], config('countries')),
            ]
        );
    }

    /**
     * Search for artist.
     *
     * Using session object retains the list of artists after Back has been
     * selected on Artist page.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function search(Request $request)
    {
        $q = $request->q;
        if ($q != ""):
            session()->put('artists_query', $q);
        endif;

        $data = $this->artist->search(session()->get('artists_query'));

        // Data object can be a view or a paginator
        if (get_class($data) === 'Illuminate\View\View'):
            return $data;
        else:
            if ($data->total() > 0):
                return view('artists', ['q' => $q, 'artists' => $data]);
            else:
                return view('artists', ['q' => $q, 'artists' => $data])->withMessage('No artists found. Try to search again!');
            endif;
        endif;
    }

    /**
     * Return autocompleted list of artists.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function autocomplete(Request $request)
    {
        return $this->artist->allByConstraints(['q' => $request->q]);
    }

}
