<?php

namespace App\Jukebox\Dictionary;

use Illuminate\Http\Request;

interface WordCloudInterface
{

    /**
     * Retrieve the word cloud.
     *
     * @param  int $id
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function get($id);

    /**
     * Retrieve word clouds
     *
     * @param  Request $request
     * @return mixed
     */
    public function all(Request $request);

    /**
     * Retrieve word clouds
     *
     * @param  array $constraints
     * @return mixed
     */
    public function allByConstraints(array $constraints);

    /**
     * Create or update a word cloud.
     *
     * @param  Request $request
     */
    public function createOrUpdate(Request $request);

    /**
    * Create a word via utility.
    *
    * @param  array $word
    * @return void
    */
    public function dynamicStore(array $word);

    /**
     * Process the song lyrics.
     *
     * @param  string $word
     * @param  string $action
     * @param  int $id
     * @return void
     */
    public function process($lyrics, $action, $id);

    /**
     * Process and store the word.
     *
     * @param  string $word
     * @return void
     */
    public function processWord($word);

}
