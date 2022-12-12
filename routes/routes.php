<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'web',
    'prefix' => 'vgcomments',
    'namespace' => 'Vigstudio\VgComment\Http\Controllers',
], function () {
    Route::group(['prefix' => 'files'], function () {
        Route::get('{uuid}.{extension}', 'FileController@stream')->name('vgcomments.files.stream');
    });
});
