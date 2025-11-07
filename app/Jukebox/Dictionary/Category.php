<?php

namespace App\Jukebox\Dictionary;

use Illuminate\Http\Request;

class Category implements CategoryInterface
{

    /**
     * Get categories
     *
     */
    public function all(Request $request)
    {
        if (empty($request->all())):
            return CategoryModel::get();
        else:
            return $this->allByConstraints($request->all());
        endif;
    }

    /**
     * Get a list of categories by constraints.
     *
     * @return array
     */
    public function allByConstraints(array $constraints = [])
    {
        if (isset($constraints['q'])):
            return $this->getJsonResults($constraints['q']);
        endif;
    }

    /**
     * Get categories
     *
     * @return Response
     */
    private function getJsonResults($q)
    {
        $data = CategoryModel::select('id', 'category as text')->where('category', 'LIKE', '%' . $q . '%')->get();
        return response()->json($data);
    }

}
