<?php

/**
 * Controller for utility requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * UtilitiesRestController handles utility REST requests.
 *
 * Standard utility REST requests such as get
 */
class UtilitiesRestController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('utilities', ['media_directory' => config('filesystems.media_directory')]);
    }

}
