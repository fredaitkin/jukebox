<?php

namespace App\Jukebox\Dictionary;

use App\JukeBox\Dictionary\DictionaryModel;
use Illuminate\Database\Eloquent\Model;

class GlossaryModel extends Model
{

    protected $connection = 'mysql3';

    protected $table = 'wn_gloss';

   /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $synset_id;

    /**
     * @var string
     */
    protected $gloss;

    /**
     * Get the word the glosssary describes.
     */
    public function word()
    {
        return $this->belongsTo(DictionaryModel::class);
    }

}
