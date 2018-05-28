<?php
namespace yedincisenol\UserProvider;

use Illuminate\Support\ServiceProvider;

class UserProviderServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/userprovider.php', 'userprovider');
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/userprovider.php' => config_path('userprovider.php')
        ], 'config');
    }

    public function provides()
    {
        return ['UserProvider'];
    }
}