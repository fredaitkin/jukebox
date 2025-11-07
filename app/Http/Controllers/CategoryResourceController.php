<?php

/**
 * Controller for category requests
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Controllers;

use App\Jukebox\Dictionary\CategoryInterface as Category;
use Illuminate\Http\Request;

/**
 * CategoryResourceController handles category requests.
 */
class CategoryResourceController extends Controller
{

    /**
     * The Category interface
     *
     * @var App\Jukebox\Dictionary\CategoryInterface
     */
    private $category;

    /**
     * Constructor
     *
     * @param App\Jukebox\Dictionary\CategoryInterface $category Category interface
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Return categories
     *
     * @param Illuminate\Http\Request $request Request object
     *
     * @return Response
     */
    public function autocomplete(Request $request)
    {
        return $this->category->allByConstraints(['q' => $request->q]);
    }

}
