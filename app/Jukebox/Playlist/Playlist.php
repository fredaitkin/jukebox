<?php

namespace App\Jukebox\Playlist;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Playlist implements PlaylistInterface
{

    /**
     * Retrieve a playlist.
     *
     * @param string $name
     */
    public function get($name)
    {
        return PlaylistModel::where(['name' => $name])->get(['playlist'])->toArray();
    }

    /**
     * Returns playlists
     *
     * @return LengthAwarePaginator Paginated list of artists.
     */
    public function all(Request $request)
    {
        if (empty($request->all()) || $request->has('page')):
            return PlaylistModel::get(['name']);
        else:
            return $this->allByConstraints($request->all());
        endif;
    }

    /**
     * Get a list of all playlist by constraints.
     *
     * @return array
     */
    public function allByConstraints(array $constraints = [])
    {
        $query = PlaylistModel::select('*');
        if (isset($constraints['playlist'])):
            $query->where('name', $constraints['playlist']);
        endif;
        if (isset($constraints['notIn'])):
            $query->where('playlist', 'not like', '%"id": "' . intval($constraints['notIn']) . '"%');
        endif;
        return $query->get();
    }

    /**
     * Create or update a playlist.
     *
     * @param Request $request
     */
    public function createOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'        => 'required|numeric',
            'playlist'  => 'required|max:10',
        ]);

        // Validate parameters
        if ($validator->fails()):
            return ['errors' => $validator->errors()->all(), 'status_code' => 422];
        endif;

        $playlist = PlaylistModel::where(['name' => $request->playlist])->first();
        if (empty($playlist)):
            $playlist = new PlaylistModel();
        endif;
        $playlist->name = $request->playlist;

        if(isset($playlist->playlist)):
            $existing_playlist = (array) json_decode($playlist->playlist);
        else:
            $playlist->playlist = [];
        endif;

        $existing_playlist[] = ['id' => $request->id, 'title' => $request->title];
        $playlist->playlist = json_encode($existing_playlist);
        $playlist->save();

        return ['status_code' => 200];
    }

    /**
     * Remove the playlist
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($name)
    {
        PlaylistModel::where(['name' => $name])->delete();
    }

}
