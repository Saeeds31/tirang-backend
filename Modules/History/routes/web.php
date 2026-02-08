<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\HistoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('histories', HistoryController::class)->names('history');
});
