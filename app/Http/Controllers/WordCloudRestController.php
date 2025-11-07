<?php

/**
 * Controller for wordcloud requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Dictionary\WordCloudInterface as WordCloud;
use Illuminate\Http\Request;

/**
 * WordCloudRestController handles wordcloud REST requests.
 *
 * Standard wordcloud REST requests such as get, post
 */
class WordCloudRestController extends Controller
{

    /**
     * The wordcloude interface
     *
     * @var App\Jukebox\Dictionary\WordCloudInterface
     */
    private $wordcloud;

    /**
     * Constructor
     *
     * @param App\Jukebox\Dictionary\WordCloudInterface $wordcloud WordCloud interface
     */
    public function __construct(WordCloud $wordcloud)
    {
        $this->wordcloud = $wordcloud;
    }

    /**
     * Display word cloud
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $words = $this->wordcloud->all($request);
        if (empty($request->all()) || $request->has('page') || $request->has('filter')):
            $view = view(
                'word_cloud', [
                    'word_cloud' => $words,
                    'filter' => $request->query('filter'),
                ]
            );
            if ($words->isEmpty()):
                $view->withMessage('No words found. Try to search again!');
            endif;
            return $view;
        endif;

        return $words;
    }

    /**
     * Show the form for editing the word.
     *
     * @param int                     $id      WordCloud id
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function edit($id, Request $request)
    {
        $wordcloud = $this->wordcloud->get($id);
        return view(
            'word', [
                'page'          => $request->page,
                'word_cloud'    => $wordcloud,
                'categories'    => json_encode($wordcloud->categories),
            ]
        );
    }

    /**
     * Update a word in the word cloud.
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Illuminate\Support\Facades\View
     */
    public function store(Request $request)
    {
        $this->wordcloud->createOrUpdate($request);
        return view(
            'word_cloud', [
                'word_cloud' => $this->wordcloud->all($request),
                'filter' => '',
            ]
        );
    }

}
