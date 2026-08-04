<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Vigstudio\VgComment\Http\Controllers\AdminController;
use Vigstudio\VgComment\Http\Controllers\Api\CommentController as ApiCommentController;
use Vigstudio\VgComment\Http\Controllers\FileController;

Route::middleware('web')
    ->prefix(Config::get('vgcomment.prefix'))
    ->group(function () {
        Route::middleware('vgcomment-moderate')
            ->prefix('admin')
            ->name('vgcomments.admin.')
            ->group(function () {
                Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
                Route::post('comments/bulk', [AdminController::class, 'bulk'])->name('comments.bulk');
                Route::put('comment/{id}/update', [AdminController::class, 'updateComment'])->name('comment.update');
                Route::delete('comment/{id}/delete', [AdminController::class, 'deleteComment'])->name('comment.delete');
                Route::put('comment/{id}/restore', [AdminController::class, 'restoreComment'])->name('comment.restore');
                Route::delete('comment/{id}/force-delete', [AdminController::class, 'forceDeleteComment'])->name('comment.force-delete');
                Route::get('setting', [AdminController::class, 'setting'])->name('setting');
                Route::post('setting', [AdminController::class, 'updateSetting'])->name('setting.post');
            });

        Route::get('files/{uuid}.{extension}', [FileController::class, 'stream'])
            ->name('vgcomments.files.stream');

        Route::prefix('api')
            ->name('vgcomments.api.')
            ->group(function () {
                Route::get('comments', [ApiCommentController::class, 'index'])->name('comments.index');
                Route::post('comments', [ApiCommentController::class, 'store'])->name('comments.store');
                Route::put('comments/{uuid}', [ApiCommentController::class, 'update'])->name('comments.update');
                Route::delete('comments/{uuid}', [ApiCommentController::class, 'destroy'])->name('comments.destroy');
                Route::post('comments/{uuid}/reactions', [ApiCommentController::class, 'react'])->name('comments.react');
                Route::delete('comments/{uuid}/reactions', [ApiCommentController::class, 'unreact'])->name('comments.unreact');
                Route::post('comments/{uuid}/report', [ApiCommentController::class, 'report'])->name('comments.report');
                Route::post('files', [ApiCommentController::class, 'upload'])->name('files.upload');
            });
    });
