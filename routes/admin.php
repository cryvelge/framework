<?php
Route::get('login', 'AuthController@showLoginForm')->name('admin.login');
Route::post('login', 'AuthController@login');
Route::get('logout', 'AuthController@logout')->name('admin.logout');

Route::get('/', 'HomeController@index')->name('home');

Route::group(['middleware' => 'auth:web'], function() {
    //
});
