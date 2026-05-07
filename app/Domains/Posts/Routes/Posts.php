<?php

use App\Domains\Posts\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('posts', PostController::class);
});