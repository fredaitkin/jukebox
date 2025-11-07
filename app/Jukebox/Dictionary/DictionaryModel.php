<?php

/**
 * DictionaryModel is a simple class that sits on a "vendor" dictionary.
 * 
 * The MySQL database is a large lexical database of English nouns, verbs,
 * adjectives and adverbs group into sets of cognitve synonyms (synsets).
 *
 * The dictionary was created by Princeton University
 * https://wordnet.princeton.edu/ WordNet. Princeton University. 2010.
 */

namespace App\Jukebox\Dictionary;

use Illuminate\Database\Eloquent\Model;

class DictionaryModel extends Model
{

    protected $connection = 'mysql3';

    protected $table = 'wn_synset';

   /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $synset_id;

    /**
     * @var integer
     */
    protected $w_num;

    /**
     * @var string
     */
    protected $ss_type;

    /**
     * The word.
     *
     * @var string
     */
    protected $word;

    /**
     *
     * @var integer
     */
    protected $sense_number;

    /**
     * @var integer
     */
    protected $tag_count;

    /**
     * Get the glossary associated with the word.
     */
    public function glossary()
    {
        return $this->hasOne(GlossaryModel::class, 'synset_id', 'synset_id');
    }

    /**
     * Get the companion dictionary definition for the word.
     */
    public function companion() {
       return $this->setConnection('mysql2')->hasMany(DictionaryPlainTextModel::class, 'word', 'word');
   }

}
