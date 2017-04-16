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

Route::get('gtcms-test-email', ['as' => 'testEmail', 'uses' => 'ContactController@testEmail']);
Route::get('fetch-sitemap', ['as' => 'fetchSitemap', 'uses' => 'PageController@sitemap']);

Route::get(trans('routes.login'), ['as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm']);
Route::post('submit-login', ['as' => 'submitLogin', 'uses' => 'Auth\LoginController@login']);
Route::get(trans('routes.logout'), ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

Route::get(trans('routes.register'), ['as' => 'register', 'uses' => 'Auth\RegisterController@showRegistrationForm']);
Route::post('submit-registration', ['as' => 'submitRegistration', 'uses' => 'Auth\RegisterController@register']);

Route::get(trans('routes.passwordReset'), ['as' => 'passwordReset', 'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm']);
Route::post('password-reset/email', ['as' => 'sendPasswordResetEmail', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
Route::get(trans('routes.passwordReset') . '/{token}', ['as' => 'passwordResetToken', 'uses' => 'Auth\ResetPasswordController@showResetForm']);
Route::post('password-reset/new-password', ['as' => 'submitNewPassword', 'uses' => 'Auth\ResetPasswordController@reset']);

Route::post('/send-message', ['as' => 'sendQuery', 'uses' => 'ContactController@handler']);

Route::get('/', ['as' => 'home', 'uses' => 'PageController@showPage']);
Route::get('{segments}', 'PageController@showPage')->where('segments', '(.*)');

Route::post('{segments}', 'PageController@show404')->where('segments', '(.*)');