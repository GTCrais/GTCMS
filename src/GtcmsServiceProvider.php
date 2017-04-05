<?php

namespace GTCrais\GTCMS;

use Illuminate\Support\ServiceProvider;

class GtcmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config' => config_path(),

            __DIR__ . '/Models' => app_path('/Models'),
            __DIR__ . '/Classes' => app_path('/Classes'),
            __DIR__ . '/Http' => app_path('/Http'),
            __DIR__ . '/Exceptions' => app_path('/Exceptions'),
			__DIR__ . '/Providers' => app_path('/Providers'),
			__DIR__ . '/Traits' => app_path('/Traits'),

            __DIR__ . '/views' => resource_path('/views'),
			__DIR__ . '/lang' => resource_path('/lang'),

			__DIR__ . '/assets' => public_path(),

			__DIR__ . '/routes' => base_path('/routes'),

			__DIR__ . '/migrations' => base_path("database/migrations"),

			__DIR__ . '/root' => base_path()
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Barryvdh\Debugbar\ServiceProvider');
        $this->app->register('Collective\Html\HtmlServiceProvider');
		$this->app->register('Intervention\Image\ImageServiceProvider');
		$this->app->register('Unisharp\Laravelfilemanager\LaravelFilemanagerServiceProvider');
    }
}
