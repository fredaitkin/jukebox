<?php

namespace App\Jukebox\Dictionary;

class Dictionary implements DictionaryInterface
{

    /**
     * Get the word.
     *
     * @param string $w Word
     * @return array
     */
    public function getWord($w)
    {
        return DictionaryModel::where(["word" => $w])->get();
    }

    /**
     * Is this a word.
     * Check this dictionary and the companion dictionary.
     *
     * @param string $w Word
     * @return boolean
     */
    public function isWord($w)
    {
        $word = DictionaryModel::where(["word" => $w])->get()->first();
        if ($word):
            return true;
        else:
            $word = new DictionaryModel;
            $word->word = $w;
            return count($word->companion) > 0;
        endif;
    }

    public function getDictionary($w)
    {
        $dictionary = [];
        $dictionary['word'] = $w;

        $glossary = [];

        $words = DictionaryModel::where(["word" => $w])->get();

        // Retrieve word glossary
        if (count($words) > 0):
            foreach($words as $definition):
                $glossary[] = ['type' => $definition['ss_type'], 'definition' => $definition['glossary']->gloss];
            endforeach;
            $word = $words[0];
        endif;

        // Retrieve companion dictionary
        if (empty($word)):
            $word = new DictionaryModel;
            $word->word = $w;
        endif;

        // TODO Dictionary should not know the format of the companion word.
        if (count($word->companion) > 0):
            foreach($word->companion as $definition):
                $glossary[] = ['type' => $definition['wordtype'], 'definition' => $definition['definition']];
            endforeach;
        endif;

        $dictionary['glossary'] = $glossary ?? [];

        return $dictionary;
    }
}
