<?php

namespace App\Jukebox\Dictionary;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{

    protected $table = 'category';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $id;

    /**
     * The category.
     *
     * @var string
     */
    protected $category;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
