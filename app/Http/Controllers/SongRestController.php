<?php

/**
 * Controller to handle standard REST requests for songs
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Song\SongInterface as Song;
use App\Jukebox\Dictionary\WordCloudInterface as WordCloud;
use Illuminate\Http\Request;
use Storage;

/**
 * SongRestController handles song REST requests.
 *
 * Standard song REST requests such as get, post
 */
class SongRestController extends Controller
{

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
        $this->song = $song;
        $this->wordCloud = $wordCloud;
    }

    /**
     * Display songs
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $songs = $this->song->all($request);
        if (empty($request->all()) || ($request->has('page') && !isset($request->genres))):
            return view('songs', ['songs' => $songs]);
        endif;

        if (isset($request->lyrics) && isset($request->id)):
            return view('lyrics', ['song' => $this->song->get($request->id)]);
        endif;

        if (isset($request->genres)):
            return view('genres', ['genres' => $this->song->getGenres()]);
        endif;

        return $songs;
    }

    /**
     * Store a newly or update a song in the database
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (isset($request->lyric_update)):
            // Lyrics only update.
            $song = $this->song->get($request->id);
            if ($request->lyrics != $song->lyrics):
                $this->wordCloud->process($request->lyrics, 'subtract', $request->id);
                $this->wordCloud->process($request->lyrics, 'add', $request->id);
                $song->lyrics = $request->lyrics;
                $song->save();
            endif;
        else:
            $this->song->createOrUpdate($request);
        endif;

        return redirect($request->url);
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
        $song = $this->song->get($id ?? null);

        if (! $song):
            abort(404);
        endif;

        // Add this to the get function?
        if (! empty($song->cover_art)):
            $cover_art = unserialize($song->cover_art);
            $cover_art = $cover_art['api'];
        endif;
        if (empty($cover_art)):
            $cover_art = '/image/cover/' . $song->id;
        endif;
        return view(
            'song', [
            'song'          => $song,
            'title'         => $song->title,
            'cover_art'     => $cover_art,
            'artists'       => json_encode($song->artists),
            'file_types'    => config('audio_file_formats'),
            'song_exists'   => Storage::disk(config('filesystems.partition'))->has(config('filesystems.media_directory') . $song->location),
            ]
        );
    }

}
