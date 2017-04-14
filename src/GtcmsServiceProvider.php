<?php

namespace GTCrais\GTCMS;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class GtcmsServiceProvider extends ServiceProvider
{
	protected $serviceProviders = [
		'Barryvdh\Debugbar\ServiceProvider',
		'Collective\Html\HtmlServiceProvider',
		'Intervention\Image\ImageServiceProvider',
		'Unisharp\Laravelfilemanager\LaravelFilemanagerServiceProvider'
	];

	protected $vendorAliases = [
		'Form' => 'Collective\Html\FormFacade',
		'Html' => 'Collective\Html\HtmlFacade',
		'Image' => 'Intervention\Image\Facades\Image'
	];

	protected $gtcmsAliases = [
		'Front' => 'Classes\Front',
		'AdminHelper' => 'Classes\AdminHelper',
		'Tools' => 'Classes\Tools',
		'ModelConfig' => 'Classes\ModelConfig',
		'Dbar' => 'Classes\Dbar',
		'PageMetaManager' => 'Classes\PageMetaManager',
		'GtcmsPremium' => 'Classes\GtcmsPremium'
	];

	protected $commands = [
		'GTCrais\GTCMS\Console\Commands\GtcmsPublish',
		'GTCrais\GTCMS\Console\Commands\GtcmsInstall'
	];

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
		// Register Service Providers
		foreach ($this->serviceProviders as $serviceProvider) {
			$this->app->register($serviceProvider);
		}

		// Register Aliases
		$aliasLoader = AliasLoader::getInstance();
		$namespace = config('gtcms.defaultNamespace', 'App');

		foreach ($this->vendorAliases as $alias => $class) {
			$aliasLoader->alias($alias, $class);
		}

		foreach ($this->gtcmsAliases as $alias => $class) {
			$aliasLoader->alias($alias, $namespace . "\\" . $class);
		}

		// Register commands
		$this->commands($this->commands);
	}
}
