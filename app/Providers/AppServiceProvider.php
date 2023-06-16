<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 767 bytes error
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // https
        if (\APP::environment(['production'])) {
            \URL::forceSchema('https');
        }
    }
}
