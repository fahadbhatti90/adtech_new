<?php

namespace App\Providers;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        // DB::listen(function($query) {
        //     Log::debug(
        //         $query->sql,
        //         $query->bindings,
        //         $query->time
        //     );
        // });

    }
}
