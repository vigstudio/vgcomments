<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

Route::group([
    'middleware' => 'web',
    'prefix' => Config::get('vgcomment.prefix'),
    'namespace' => 'Vigstudio\VgComment\Http\Controllers',
], function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', 'AdminController@dashboard')->name('vgcomments.admin.dashboard');

        Route::put('comment/{id}/update', 'AdminController@updateComment')->name('vgcomments.admin.comment.update');
        Route::delete('comment/{id}/delete', 'AdminController@deleteComment')->name('vgcomments.admin.comment.delete');

        Route::get('setting', 'AdminController@setting')->name('vgcomments.admin.setting');
        Route::post('setting', 'AdminController@updateSetting')->name('vgcomments.admin.setting.post');
    });

    Route::group(['prefix' => 'files'], function () {
        Route::get('{uuid}.{extension}', 'FileController@stream')->name('vgcomments.files.stream');
    });
});
