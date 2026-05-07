<?php

use App\Domains\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('users', UserController::class);
});