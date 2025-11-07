<?php

namespace App\Jukebox\Playlist;

use Illuminate\Http\Request;

interface PlaylistInterface
{

    /**
     * Retrieve a playlist.
     *
     * @param  string $name
     * @return array
     */
    public function get($id);

    /**
     * Retrieve playlists
     *
     * @param  Request $request
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all(Request $request);

    /**
     * Retrieve playlists by constraints.
     *
     * @param  array $constraints
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function allByConstraints(array $constraints = []);

    /**
     * Create or update a playlist.
     *
     * @param  Request $request
     * @return array
     */
    public function createOrUpdate(Request $request);

    /**
     * Remove the playlist
     *
     * @param  string  $name
     * @return void
     */
    public function delete($name);

}
