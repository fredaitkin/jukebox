<?php

/**
 * Controller for query requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * QueryRestController handles query REST requests.
 *
 * Standard query REST requests such as get
 */
class QueryRestController extends Controller
{

    /**
     * Display page
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return view('query', ['show_cols' => 1]);
    }

}
