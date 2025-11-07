<?php

namespace App\Jukebox\Dictionary;

class DictionaryPlainText
{

    /**
     * Is this a word.
     *
     * @param string $w Word
     * @return boolean
     */
    public function isWord($w)
    {
        $word = DictionaryPlainTextModel::whereRAW('binary LOWER(word) = binary ?', $w)->get()->first();
        return isset($word);
    }

    public function getDictionary($w)
    {
        $dictionary = [];

        $word = DictionaryPlainTextModel::whereRAW('binary LOWER(word) = binary ?', $w)->get();

        if (empty($word[0])):
            return $dictionary;
        endif;

        $glossary = [];
        foreach($word as $definition):
            $glossary[] = ['type' => $definition['wordtype'], 'definition' => $definition['definition']];
        endforeach;
        $dictionary['word'] = $word[0]['word'];
        $dictionary['glossary'] = $glossary;
        return $dictionary;
    }

}
