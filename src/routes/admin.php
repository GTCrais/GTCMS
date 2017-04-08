<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/', array('uses' => 'AdminController@index'));

Route::match(array('get', 'post'), "/login", array('as' => 'adminLogin', 'uses' => 'AdminController@login'));
Route::get("/logout", array('as' => 'adminLogout', 'uses' => 'AdminController@logout'));
Route::get("/excelExport/{modelName}", array('as' => 'excelExport', 'uses' => 'AdminController@excelExport'));
Route::get("/access-denied", array('as' => 'restricted', 'uses' => 'AdminController@restricted'));
Route::match(array('get', 'post'), "/gtcms-database", array('as' => 'gtcmsDatabase', 'uses' => 'AdminController@database'));
Route::match(array('get', 'post'), "/gtcms-optimize", array('as' => 'gtcmsOptimize', 'uses' => 'AdminController@optimize'));
Route::get('/setNavigationSize', array('as' => 'setNavigationSize', 'uses' => 'AdminController@setNavigationSize'));
Route::post('/ajaxUpdate', array('as' => 'ajaxUpdate', 'uses' => 'AdminController@ajaxUpdate'));

Route::post('{entityName}/{action?}/{id?}', array("uses" => "AdminEntityController@handleAction"))
	->where(array('id' => '([0-9]+|new)', 'action' => '(add|edit|delete)'));

Route::get('{entityName}/{action?}/{id?}', array("uses" => "AdminEntityController@handleAction"))
	->where(array('id' => '([0-9]+|new)', 'action' => '(add|edit|delete|ajaxMove|ajaxSearch)'));

Route::match(array('get', 'post'), "/{entityName}/{fileAction}/{fileNameField}/{id}", array("uses" => "AdminController@handleFile"))
	->where(array('fileAction' => 'uploadFile|uploadImage|deleteFile', 'id' => '([0-9]+|new|new_gtcms_entry)'));