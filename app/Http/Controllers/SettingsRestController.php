<?php

/**
 * Controller for settings requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * SettingsRestController handles settings REST requests.
 *
 * Standard settings REST requests such as get
 */
class SettingsRestController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('settings', ['media_directory' => config('filesystems.media_directory')]);
    }

}
