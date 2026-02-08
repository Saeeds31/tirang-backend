<?php

use Illuminate\Support\Facades\Route;
use Modules\Image\Http\Controllers\ImageController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('images', ImageController::class)->names('image');
});
