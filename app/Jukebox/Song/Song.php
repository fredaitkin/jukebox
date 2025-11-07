<?php

namespace App\Jukebox\Song;

use Illuminate\Http\Request;

use DB;

class Song implements SongInterface
{

    /** Basic Routines */

    /**
     * Retrieve a song.
     *
     * @param int $id
     */
    public function get($id)
    {
        return SongModel::find($id);
    }

    /**
     * Get a list of all songs.
     *
     * @return array
     */
    public function all(Request $request)
    {
        if (empty($request->all()) || $request->has('page')):
            return SongModel::paginate();
        else:
            return $this->allByConstraints($request->all());
        endif;
    }

    /**
     * Get a list of all songs by constraints.
     *
     * @return array
     */
    public function allByConstraints(array $constraints = [], $fields = [])
    {
        if ($fields):
            $query = SongModel::select(DB::raw(implode(",", $fields)))->with('artists:artist');
        else:
            $query = SongModel::select('songs.*')->with('artists:artist');
        endif;

        // Get songs for an album
        if (isset($constraints['id']) && isset($constraints['album']) && $constraints['album'] == 'true'):
            return $this->getAlbumSongs($constraints['id']);
        endif;

        if (isset($constraints['id'])):
            $query->where('id', $constraints['id']);
        endif;

        if (isset($constraints['artist'])):
            $artist = $constraints['artist'];
            $exact_match = $constraints['exact_match'];
            $query->whereHas('artists', function($q) use($artist, $exact_match) {
                if ($exact_match == 'true'):
                    $q->where('artist', $artist);
                else:
                    $q->where('artist', 'LIKE', '%' . $artist . '%');
                endif;
            });
        endif;

        if (isset($constraints['year'])):
            $query->where('year', $constraints['year']);
        endif;

        if (isset($constraints['genre'])):
            $query->where('genre', $constraints['genre']);
        endif;

        if (isset($constraints['album'])):
            $query->where('album', $constraints['album']);
        endif;

        if (isset($constraints['location'])):
            $query->where('location', $constraints['location']);
        endif;

        if (isset($constraints['ids'])):
            $query->whereIn('id', $constraints['ids']);
        endif;

        if (isset($constraints['cover_art_empty'])):
            $query->whereNull('cover_art');
        endif;

        if (isset($constraints['lyrics'])):
            $query->where('lyrics', 'LIKE', "%{$constraints['lyrics']}%");
        endif;

        if(isset($constraints['exempt'])):
            $query->whereNotIn('songs.id', explode(',', $constraints['exempt']));
        endif;

        if(isset($constraints['lyrics_empty'])):
            $query->whereIn('lyrics', ['','unavailable']);
        endif;

        if(isset($constraints['composer_empty'])):
            $query->whereNull('composer');
        endif;

        if(isset($constraints['do_not_play'])):
            $query->whereNull('do_not_play');
        endif;

        return $query->get();
    }

    /**
     * Create or update a song.
     *
     * @param Request $request
     */
    public function createOrUpdate(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required|max:255',
            'album' => 'required|max:255',
            'year'  => 'required|integer',
            'rank'  => 'nullable|integer|between:1,5',
        ]);

        if (isset($request->id)):
            $model = SongModel::find($request->id);
        else:
            $model = new SongModel();
        endif;

        $model->title = $request->title;
        $model->album = $request->album;
        $model->year = $request->year;
        $model->file_type = $request->file_type;
        $model->track_no = $request->track_no;
        $model->genre = $request->genre;
        $model->location = $request->location;
        $model->filesize = $request->filesize ?? 0;
        $model->composer = $request->composer;
        $model->playtime = $request->playtime;
        $model->notes = $request->notes;
        $model->rank = $request->rank;
        $model->do_not_play = isset($request->do_not_play);

        $model->save();

        // Make any updates to artist/s
        $existing_artists = [];
        foreach($model->artists as $artist):
            $existing_artists[] = $artist->id;
        endforeach;

        if (empty($request->artists)):
            $request->artists = [];
        endif;
        $inserts = array_diff($request->artists, $existing_artists);
        foreach($inserts as $artist):
            $model->artists()->attach(['artist' => $artist]);
        endforeach;
        $deletes = array_diff($existing_artists, $request->artists);
        foreach($deletes as $artist):
            $model->artists()->detach(['artist' => $artist]);
        endforeach;
    }

    /**
    * Search for songs
    *
    * @param string $query
    */
    public function search($query) {
        return SongModel::select('songs.*')
            ->whereHas('artists', function($q) use($query) {
                $q->where('artist', 'LIKE', '%' . $query . '%');
            })
            ->orWhere('title', 'LIKE', '%' . $query . '%')
            ->orWhere('album', 'LIKE', '%' . $query . '%')
            ->orWhere('songs.notes', 'LIKE', '%' . $query . '%')
            ->paginate()
            ->appends(['q' => $query])
            ->setPath('');
    }

    /** Utility Routines */

    /**
     * Create a song via the music loading process.
     *
     * @param string path
     * @param string album_name
     * @param integer artist_it
     * @param array ID3 song array
     *
     */
    public function dynamicStore($path, $album_name, $artist_id, $song)
    {
        $model = new SongModel();

        $model->title = $song->title();
        $model->album = $album_name;
        $model->year = $song->year();
        $model->file_type = $song->fileType();
        $model->track_no = $song->trackNo();
        $model->genre = $song->genre();
        $model->location = $path;
        $model->filesize = $song->fileSize();
        $model->composer = $song->composer();
        $model->playtime = $song->playtime();
        $model->notes = $song->notes();

        $model->save();

        $model->artists()->attach(['artist' => $artist_id]);
    }

    /**
     * Does the album exist
     *
     * @param integer $id Artist id
     * @param string $album_name Album name
     * @return boolean
     */
    public function doesAlbumExist($id, $album_name)
    {
        $song = SongModel::where('album', $album_name)
            ->with(['artists' => function($q) use ($id) {
                $q->where('artist_id', '=', $id);
            }],)
            ->first();
        return isset($song);
    }

    /**
     * Does the song exist
     *
     * @param integer $id Artist id
     * @param string $title Song title
     * @return boolean
     */

    public function doesSongExist($id, $title)
    {
        $songs = SongModel::where('title', $title)->get();
        foreach($songs as $song):
            foreach ($song->artists as $artist):
                if ($artist->id == $id):
                    return true;
                endif;
            endforeach;
        endforeach;
        return false;
    }

    /**
    * Is this file an audio file type?
    *
    * @param string $file
    */
    public function isSong($file) {
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
        return in_array($extension, config('audio_file_formats'));
    }

    /** Artist Routines */

    /**
    * Retrieve artist's songs.
    *
    * Retrieves the songs from the artist's albums and compilation albums.
    *
    * @param int $id
    * @param string $artist
    */
    public function getArtistSongs($id, $artist) {
        return SongModel::select('id', 'title')
            ->whereHas('artists', function($q) use ($id) {
                $q->where('artist_id', '=', $id);
            })
            ->orWhere(["notes" => $artist])
            ->orderBy('title')
            ->get();
    }

    /** Other Routines */

    /**
     * Update a song.
     *
     * @param array $song
     */
    public function updateSong(array $song)
    {
        $model = new SongModel();

        if (! isset($song['id']) || ! is_numeric($song['id'])):
            throw new Exception('A numeric song id is required');
        endif;

        $model = $model->find($song['id']);

        if (isset($song['lyrics'])):
            $model->lyrics = $song['lyrics'];
        endif;

        $model->update();
    }

    /**
    * Get song genres
    */
    public function getGenres()
    {
        return SongModel::select('genre')->where('genre', '>', '')->groupBy('genre')->paginate();
    }

    /**
     * Retrieve songs with English lyrics.
     *
     * @param array $ids
     *   Song ids.
     */
    public function retrieveEnglishLyrics(array $ids = null)
    {
        $query = SongModel::select('songs.id', 'title', 'lyrics')
            ->join('artist_song', 'songs.id', '=', 'artist_song.song_id')
            ->whereNotIn('songs.id', [
                908, 911, 1273, 1425, 2225, 3966, 3994, 4145, 4146, 4885, 8587, 4856, 9473, 9741,
            ])
            ->whereNotIn('artist_song.artist_id', [
                23, 84, 197, 209, 280, 469, 510, 611, 763, 802, 821, 838, 841, 846, 1453, 1516,
            ])
            ->whereNotIn('album', [
                'Turkish Groove', 'African Women', 'Bocelli Greatest Hits', 'Buena Vista Social Club', 'Everything Is Possible!',
                "Edith Piaf - 20 'French' Hit Singles",
            ])
            ->whereNotIn('lyrics', ['unavailable', 'Instrumental', 'inapplicable']);

        if ($ids):
            $query->whereIn('songs.id', $ids);
        endif;

        return $query->get()->toArray();
    }

    /**
     * Retrieve songs for an album
     *
     * @param int $song_id
     *
     * @return array
     */
    private function getAlbumSongs($song_id)
    {
        $song = SongModel::find($song_id);
        $songs = $song->artists[0]->songs;
        foreach($songs as $k => $v):
            if ($v->album != $song->album):
                unset($songs[$k]);
            endif;
        endforeach;
        // Reset index to 0.
        return $songs->values();
    }

}
