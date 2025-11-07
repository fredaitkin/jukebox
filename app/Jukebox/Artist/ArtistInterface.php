<?php

namespace App\Jukebox\Artist;

use Illuminate\Http\Request;

interface ArtistInterface
{

    /**
     * Retrieve an artist.
     *
     * @param  int $id
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function get($id);

    /**
     * Retrieve artists
     *
     * @param  Request $request
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all(Request $request);

    /**
     * Retrieve artists
     *
     * @param  array $constraints
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function allByConstraints(array $constraints);

    /**
     * Create or update an artist.
     *
     * @param  Request $request
     */
    public function createOrUpdate(Request $request);

    /**
    * Search for artists
    *
    * @param string $query
    * @return Illuminate\Database\Eloquent\Collection
    */
    public function search($query);

    /**
    * Create an artist via the music loading process.
    *
    * @param  array $artist
    * @return int
    */
    public function dynamicStore(array $artist);

    /**
     * Does the artist exist
     *
     * @param  string $artist_name
     * @return mixed
     */
    public function getID($artist_name);

    /**
     * Is the "artist" a Compilation?
     *
     * @param  int $id
     * @return bool
     */
    public function isCompilation($id);

    /**
    * Retrieve an artistsby name
    *
    * @param  string $search
    * @return Illuminate\Database\Eloquent\Collection
    */
    public function searchByName($search);

    /**
    * Retrieve artists albums
    *
    * @param  int $id
    * @return array
    */
    public function getArtistAlbums($id);

}
