<?php

namespace ZeroConfig\FileCabinet;
// see how to add autoload in your package below

// make sure you extend your class from ServiceProvider
use Illuminate\Support\ServiceProvider;

class  FileCabinetServiceProvider extends ServiceProvider
{
    // your content here

    // Must have 2 methods

    // method 1
    public function boot()
    {
        // content added
//        require_once ( __DIR__ . '/app/Http/Controllers/FileCabinetController.php'); // this has been deprecated
        require_once ( __DIR__ . '/app/Http/Controllers/UploadFileController.php');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // register views
//        $this->loadViewsFrom(__DIR__.'/resources/views/file_cabinets', 'file_cabinets'); // this has been deprecated




    }


    // method 2
    public function register()
    {

    }

}
