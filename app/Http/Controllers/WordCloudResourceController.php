<?php

/**
 * Controller for wordCloud requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Dictionary\WordCloudInterface as WordCloud;
use Illuminate\Http\Request;

/**
 * WordCloudResourceController handles wordCloud requests.
 *
 * Handles wordCloud requests.
 */
class WordCloudResourceController extends Controller
{

    /**
     * The wordcloude interface
     *
     * @var App\Jukebox\Dictionary\WordCloudInterface
     */
    private $wordCloud;

    /**
     * Constructor
     *
     * @param App\Jukebox\Dictionary\WordCloudInterface $wordCloud WordCloud interface
     */
    public function __construct(WordCloud $wordCloud)
    {
        $this->wordCloud = $wordCloud;
    }


    /**
     * Search for word.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function autocomplete(Request $request)
    {
        $wordCloud = $this->wordCloud->allByConstraints(['like' => $request->search]);
        $data = [];
        foreach($wordCloud as $word):
            $data[] = ['value' => $word->id, 'label' => $word->word];
        endforeach;
        return response()->json($data);
    }

}
