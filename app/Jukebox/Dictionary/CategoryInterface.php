<?php

namespace App\Jukebox\Dictionary;

use Illuminate\Http\Request;

interface CategoryInterface
{

    /**
     * Retrieve categories
     *
     * @param  Request $request
     * @return mixed
     */
    public function all(Request $request);

    /**
     * Retrieve categories
     *
     * @param  array $constraints
     * @return mixed
     */
    public function allByConstraints(array $constraints);

}
