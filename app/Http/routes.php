<?php

Route::get('/', ['as' => 'projects', 'uses' => 'ProjectsController@index']);
Route::get('/projects/create', ['as' => 'projects.create', 'uses' => 'ProjectsController@create']);
Route::get('/projects/{id}', ['as' => 'projects.show', 'uses' => 'ProjectsController@show'])->where('id', '[0-9]+');
Route::post('/projects', ['as' => 'projects.store', 'uses' => 'ProjectsController@store']);
Route::get('/projects/{id}/edit', ['as' => 'projects.edit', 'uses' => 'ProjectsController@edit'])->where('id', '[0-9]+');
Route::get('/projects/{id}/badges', ['as' => 'projects.badges', 'uses' => 'ProjectsController@badges'])->where('id', '[0-9]+');
Route::post('/projects/{id}/code', ['as' => 'projects.code', 'uses' => 'ProjectsController@getCode'])->where('id', '[0-9]+');
Route::post('/projects/{id}', ['as' => 'projects.update', 'uses' => 'ProjectsController@update'])->where('id', '[0-9]+');
Route::post('/projects/{id}/badges', ['as' => 'projects.updateBadge', 'uses' => 'ProjectsController@updateBadge'])->where('id', '[0-9]+');
Route::get('/projects/{id}/delete', ['as' => 'projects.delete', 'uses' => 'ProjectsController@delete'])->where('id', '[0-9]+');

Route::get('/review', ['as' => 'review.index', 'uses' => 'ReviewController@index']);
Route::get('/review/{id}/results', ['as' => 'review.results', 'uses' => 'ReviewController@results'])->where('id', '[0-9]+');
Route::post('/review/{id?}', ['as' => 'review.generate', 'uses' => 'ReviewController@generate'])->where('id', '[0-9]+');

Route::get('/review/api/badges/user/{userEmail}', ['middleware' => 'cors', 'as' => 'review.userbadges', 'uses' => 'BadgeController@getUserBadges']);
Route::get('/review/api/badges/{projectName}/{userEmail}', ['middleware' => 'cors', 'as' => 'review.badges', 'uses' => 'BadgeController@getBadges']);
Route::get('/review/api/badges/', ['middleware' => 'cors', 'as' => 'review.allbadges', 'uses' => 'BadgeController@getAllBadges']);
Route::get('/review/api/{name}/{from}/{to}', ['middleware' => 'cors', 'as' => 'review.generateapi', 'uses' => 'ReviewController@generateApi']);

Route::get('/review/{id}', ['as' => 'review.analyze', 'uses' => 'ReviewController@analyze'])->where('id', '[0-9]+');

Route::get('/api/{name}/{from}/{to}', ['middleware' => 'cors', 'as' => 'api.data', 'uses' => 'ApiController@getData']);
Route::get('/api/{projectName}/{userEmail}', ['middleware' => 'cors', 'as' => 'api.data', 'uses' => 'BadgeController@getBadges']);

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

	