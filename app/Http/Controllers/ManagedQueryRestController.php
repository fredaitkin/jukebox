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
 * ManagedQueryRestController handles query REST requests.
 *
 * Standard query REST requests such as get
 */
class ManagedQueryRestController extends Controller
{

    /**
     * Display managed query page.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $queries = [
            '' => 'Please Select',
            '1' => 'Words in Songs',
        ];
        return view('managed_query', ['queries' => $queries]);
    }

}
