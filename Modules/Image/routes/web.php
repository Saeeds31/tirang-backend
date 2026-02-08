<?php

use Illuminate\Support\Facades\Route;
use Modules\Image\Http\Controllers\ImageController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('images', ImageController::class)->names('image');
});
