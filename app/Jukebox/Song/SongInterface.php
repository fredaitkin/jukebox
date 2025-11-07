<?php

namespace App\Jukebox\Song;

use Illuminate\Http\Request;

interface SongInterface
{

    /**
     * Retrieve a song.
     *
     * @param  int $id
     * @return \App\Jukebox\Song\SongModel
     */
    public function get($id);

    /**
     * Retrieve all songs;
     *
     * @param  Request $request
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all(Request $request);

    /**
     * Retrieve all songs
     *
     * @param  array $constraints
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function allByConstraints(array $constraints);

    /**
     * Create a song.
     *
     * @param  Request  $request
     * @return void
     */
    public function createOrUpdate(Request $request);

    /**
    * Search for songs
    *
    * @param string $query
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function search($query);

    /**
     * Create a song via the music loading process.
     *
     * @param  string path
     * @param  string album_name
     * @param  int artist_it
     * @param  array ID3 song array
     * @return void
     */
    public function dynamicStore($path, $album_name, $artist_id, $song);

    /**
     * Does the album exist
     *
     * @param  integer $id Artist id
     * @param  string $album_name Album name
     * @return bool
     */
    public function doesAlbumExist($id, $album_name);

    /**
     * Does the song exist
     *
     * @param  int $id Artist id
     * @param  string $title Song title
     * @return bool
     */

    public function doesSongExist($id, $title);

    /**
    * Is this file an audio file type?
    *
    * @param  string $file
    * @return bool
    */
    public function isSong($file);

    /**
    * Retrieve artist's songs.
    *
    * Retrieves the songs from the artist's albums and compilation albums.
    *
    * @param  int $id
    * @param  string $artist
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function getArtistSongs($id, $artist);

    /**
     * Update a song.
     *
     * @param  array $song
     * @return void
     */
    public function updateSong(array $song);

    /**
    * Get song genres
    *
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function getGenres();

    /**
     * Retrieve songs with English lyrics.
     *
     * @param  array $ids
     * @return array
     */
    public function retrieveEnglishLyrics(array $ids = null);
}
