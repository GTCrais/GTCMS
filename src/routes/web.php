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

View::share('cPage', false);
View::share('loggedUser', Auth::user());

//Route::post(trans('routes.login'), array('as' => 'login', 'uses' => 'UserController@login'));
//Route::get(trans('routes.logout'), array('as' => 'logout', 'uses' => 'UserController@logout'));
//Route::match(array('GET', 'POST'), trans('routes.register'), array('as' => 'register', 'uses' => 'UserController@register'));

Route::post('/send-message', array('as' => 'sendQuery', 'uses' => 'ContactController@handler'));

Route::get('/', array('as' => 'home', 'uses' => 'PageController@showPage'));
Route::get('{segments}', 'PageController@showPage')->where('segments', '(.*)');

Route::post('{segments}', 'PageController@show404')->where('segments', '(.*)');