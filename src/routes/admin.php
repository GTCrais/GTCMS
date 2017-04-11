<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/', ['uses' => 'AdminController@index']);

Route::match(['get', 'post'], "/login", ['as' => 'adminLogin', 'uses' => 'AdminController@login']);
Route::get("/logout", ['as' => 'adminLogout', 'uses' => 'AdminController@logout']);
Route::get("/excelExport/{modelName}", ['as' => 'excelExport', 'uses' => 'AdminController@excelExport']);
Route::get("/access-denied", ['as' => 'restricted', 'uses' => 'AdminController@restricted']);
Route::match(['get', 'post'], "/gtcms-database", ['as' => 'gtcmsDatabase', 'uses' => 'AdminController@database']);
Route::match(['get', 'post'], "/gtcms-optimize", ['as' => 'gtcmsOptimize', 'uses' => 'AdminController@optimize']);
Route::get('/setNavigationSize', ['as' => 'setNavigationSize', 'uses' => 'AdminController@setNavigationSize']);
Route::post('/ajaxUpdate', ['as' => 'ajaxUpdate', 'uses' => 'AdminController@ajaxUpdate']);

Route::post('{entityName}/{action?}/{id?}', ["uses" => "AdminEntityController@handleAction"])
	->where(['id' => '([0-9]+|new)', 'action' => '(add|edit|delete)']);

Route::get('{entityName}/{action?}/{id?}', ["uses" => "AdminEntityController@handleAction"])
	->where(['id' => '([0-9]+|new)', 'action' => '(add|edit|delete|ajaxMove|ajaxSearch)']);

Route::match(['get', 'post'], "/{entityName}/{fileAction}/{fileNameField}/{id}", ["uses" => "AdminController@handleFile"])
	->where(['fileAction' => 'uploadFile|uploadImage|deleteFile', 'id' => '([0-9]+|new|new_gtcms_entry)']);