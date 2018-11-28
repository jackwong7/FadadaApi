<?php
/**
 * Created by PhpStorm.
 * User: JackWong
 * Date: 2018/11/28
 * Time: 9:47
 */

namespace JackWong\Fadada;

use Illuminate\Support\ServiceProvider;

class FadadaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //publish config file
        $this->publishes([__DIR__.'/../config/fadada.php' => config_path('fadada.php'),'config']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // merge configs
        $this->mergeConfigFrom(__DIR__.'/../config/fadada.php','fadada');
    }
}