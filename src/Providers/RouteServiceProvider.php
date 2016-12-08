<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        //$this->mapApiRoutes();

		$adminRoute = $this->mapAdminRoutes();

		if (!$adminRoute) {
			$this->mapWebRoutes();
		}

		if (!config('gtcms.cmsPrefix')) {

			// No prefix means we need to catch all non-existent
			// routes, and redirect them back to Admin. We're doing
			// this here instead of in 'routes/admin.php' because we
			// don't want this default route to be checked
			// by AdminAuth Middleware

			\Route::match(['post', 'get'], '{segments}', $this->namespace . '\AdminController@redirectToAdmin')->where('segments', '(.*)');
		}
    }

	protected function mapAdminRoutes()
	{
		\App::setLocale(config('gtcmslang.defaultAdminLocale'));

		\Route::group([
			'prefix' => config('gtcms.cmsPrefix'),
			'middleware' => ['web', 'adminAuth'],
			'namespace' => $this->namespace,
		], function ($router) {
			require base_path('routes/admin.php');
		});

		return (!config('gtcms.cmsPrefix') || \Request::segment(1) == config('gtcms.cmsPrefix')) ? true : false;
	}

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
		$languages = config('gtcmslang.languages');
		$defaultLocale = config('gtcmslang.defaultLocale');
		$siteIsMultilingual = config('gtcms.premium') && config('gtcmslang.siteIsMultilingual');

		$locale = \Request::segment(1);
		if (in_array($locale, $languages) && $locale != $defaultLocale && $siteIsMultilingual) {
			\App::setLocale($locale);
		} else {
			\App::setLocale($defaultLocale);
			$locale = null;
		}

        \Route::group([
			'prefix' => $locale,
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        \Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
