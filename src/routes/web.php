<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

View::share('cPage', new \App\Models\Page());

Route::get(trans('routes.login'), array('as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm'));
Route::post('submit-login', array('as' => 'submitLogin', 'uses' => 'Auth\LoginController@login'));
Route::get(trans('routes.logout'), array('as' => 'logout', 'uses' => 'Auth\LoginController@logout'));

Route::get(trans('routes.register'), array('as' => 'register', 'uses' => 'Auth\RegisterController@showRegistrationForm'));
Route::post('submit-registration', array('as' => 'submitRegistration', 'uses' => 'Auth\RegisterController@register'));

Route::post('/send-message', ['as' => 'sendQuery', 'uses' => 'ContactController@handler']);

Route::get('/', ['as' => 'home', 'uses' => 'PageController@showPage']);
Route::get('{segments}', 'PageController@showPage')->where('segments', '(.*)');

Route::post('{segments}', 'PageController@show404')->where('segments', '(.*)');