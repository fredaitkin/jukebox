<?php

namespace App\Jukebox\Dictionary;

interface DictionaryInterface
{

    /**
     * Is this a word.
     *
     * @param  string $w
     * @return bool
     */
    public function isWord($w);

    /**
     * Get the word.
     *
     * @param  string $w
     * @return array
     */
    public function getWord($w);

    /**
     * Get definition for word
     *
     * @param  string $w
     * @return array
     */
    public function getDictionary($w);

}
