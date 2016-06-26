<?php

/* GTCMS routes */

$adminRoute = false;
if (Request::segment(1) == 'admin') $adminRoute = true;

Route::group(array('prefix' => 'admin', 'middleware' => 'adminAuth'), function() {

	App::setLocale(config('gtcmslang.defaultAdminLocale'));

	Route::get('/', array('uses' => 'AdminController@index'));

	/* -------- EXCEPTIONS -------- */


	/* -------- END EXCEPTIONS -------- */

	Route::match(array('get', 'post'), "/login", array('as' => 'adminLogin', 'uses' => 'AdminController@login'));
	Route::get("/logout", array('as' => 'adminLogout', 'uses' => 'AdminController@logout'));
	Route::get("/excelExport/{modelName}", array('as' => 'excelExport', 'uses' => 'AdminController@excelExport'));
	Route::get("/access-denied", array('as' => 'restricted', 'uses' => 'AdminController@restricted'));
	Route::match(array('get', 'post'), "/update-languages", array('as' => 'updateLanguages', 'uses' => 'AdminController@updateLanguages'));
	Route::match(array('get', 'post'), "/optimize", array('as' => 'optimize', 'uses' => 'AdminController@optimize'));
	Route::get('/setNavigationSize', array('as' => 'setNavigationSize', 'uses' => 'AdminController@setNavigationSize'));
	Route::post('/ajaxUpdate', array('as' => 'ajaxUpdate', 'uses' => 'AdminController@ajaxUpdate'));

	Route::post('{entityName}/{action?}/{id?}', array("uses" => "AdminEntityController@handleAction"))
		->where(array('id' => '([0-9]+|new)', 'action' => '(add|edit|delete)'));

	Route::get('{entityName}/{action?}/{id?}', array("uses" => "AdminEntityController@handleAction"))
		->where(array('id' => '([0-9]+|new)', 'action' => '(add|edit|delete|ajaxMove|ajaxSearch)'));

	Route::match(array('get', 'post'), "/{entityName}/{fileAction}/{fileNameField}/{id}", array("uses" => "AdminController@handleFile"))
		->where(array('fileAction' => 'uploadFile|uploadImage|deleteFile', 'id' => '([0-9]+|new|new_gtcms_entry)'));

});

/* Front routes */

if (!$adminRoute) {

	$languages = config('gtcmslang.languages');
	$defaultLocale = config('gtcmslang.defaultLocale');
	$siteIsMultilingual = config('gtcms.premium') && config('gtcmslang.siteIsMultilingual');

	$locale = Request::segment(1);
	if (in_array($locale, $languages) && $locale != $defaultLocale && $siteIsMultilingual) {
		App::setLocale($locale);
	} else {
		App::setLocale($defaultLocale);
		$locale = null;
	}

	View::share('cPage', false);
	View::share('loggedUser', Auth::user());

	Route::group(array('prefix' => $locale), function() {

		//Route::post(trans('routes.login'), array('as' => 'login', 'uses' => 'UserController@login'));
		//Route::get(trans('routes.logout'), array('as' => 'logout', 'uses' => 'UserController@logout'));
		//Route::match(array('GET', 'POST'), trans('routes.register'), array('as' => 'register', 'uses' => 'UserController@register'));

		Route::post('/send-message', array('as' => 'sendQuery', 'uses' => 'ContactController@handler'));

		Route::get('/', array('as' => 'home', 'uses' => 'PageController@showPage'));
		Route::get('{segments}', 'PageController@showPage')->where('segments', '(.*)');

		Route::post('{segments}', 'PageController@show404')->where('segments', '(.*)');

	});

}