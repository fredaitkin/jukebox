<?php

/**
 * Controller for dictionary requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Dictionary\DictionaryInterface as Dictionary;
use Illuminate\Http\Request;

/**
 * DictionaryResourceController handles dictionary requests
 */
class DictionaryResourceController extends Controller
{

    /**
     * The dictionary interface
     *
     * @var App\Jukebox\Dictionary\DictionaryInterface
     */
    private $dictionary;

    /**
     * Constructor
     *
     * @param App\Jukebox\Dictionary\DictionaryInterface $dictionary Dictionary interface
     */
    public function __construct(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * Display dictionary information for a word
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function dictionary(Request $request)
    {
        return $this->dictionary->getDictionary($request->get('word'));
    }

}
