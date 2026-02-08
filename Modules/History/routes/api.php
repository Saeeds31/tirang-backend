<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\HistoryController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('histories', HistoryController::class)->names('history');
});
