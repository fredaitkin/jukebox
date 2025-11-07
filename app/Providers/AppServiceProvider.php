<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Jukebox\Song\SongInterface',
            'App\Jukebox\Song\Song'
        );

        $this->app->bind(
            'App\Jukebox\Artist\ArtistInterface',
            'App\Jukebox\Artist\Artist'
        );

        $this->app->bind(
            'App\Jukebox\Playlist\PlaylistInterface',
            'App\Jukebox\Playlist\Playlist'
        );

        $this->app->bind(
            'App\Jukebox\Dictionary\WordCloudInterface',
            'App\Jukebox\Dictionary\WordCloud',
        );
        $this->app->bind(
            'App\Jukebox\Dictionary\CategoryInterface',
            'App\Jukebox\Dictionary\Category',
        );

        $this->app->bind(
            'App\Jukebox\Dictionary\DictionaryInterface',
            'App\Jukebox\Dictionary\Dictionary',
        );

    }
}
