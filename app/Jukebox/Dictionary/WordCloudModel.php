<?php

namespace App\Jukebox\Dictionary;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class WordCloudModel extends Model
{

    use Sortable;

    /**
     * Use timestamps
     *
     * @var bool
     */
    public $timestamps = true;

    protected $table = 'word_cloud';

    /**
     * The primary key for the model.
     *
     * @var integer
     */
    protected $id;

    /**
     * The word.
     *
     * @var string
     */
    protected $word;

    /**
     * Number of times the word appears in lyrics.
     *
     * @var integer
     */
    protected $count;

    /**
     * Recognized as word by the two dictionary databases.
     *
     * @var bool
     */
    protected $is_word;

    /**
     * Category.
     *
     * @var string
     */
    protected $category;

    /**
     * The id of the word this word is the variant of.
     *
     * @var integer
     */
    protected $variant_of;

    /**
     * Created date.
     *
     * @var datetime
     */
    protected $created_at;

    /**
     * Updated date.
     *
     * @var datetime
     */
    protected $updated_at;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The number of records to return for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * Word cloud categories
     */
    public function categories()
    {
        // Specify table name as it does not follow the default laravel naming convention
        return $this->belongsToMany('App\Jukebox\Dictionary\CategoryModel', 'word_category', 'word_cloud_id', 'category_id');
    }

    /**
     * Word cloud categories for display
     */
    public function getCategoryDisplayAttribute()
    {
        $categories = [];
        foreach ($this->categories as $category):
            $categories[] = $category->category;
        endforeach;
        return implode(',', $categories);
    }

    /**
     * Get the category ids as an array
     * @return string
     */
    public function getCategoryArrayAttribute()
    {
        $categories = [];
        foreach ($this->categories as $category):
            $categories[] = $category->id;
        endforeach;
        return $categories;
    }

    /**
     * Word cloud songs
     */
    public function songs()
    {
        return $this->belongsToMany('App\Jukebox\Song\SongModel', 'song_word_cloud', 'word_cloud_id', 'song_id');
    }

}
