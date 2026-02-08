<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioCategory\Http\Controllers\PortfolioCategoryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('portfoliocategories', PortfolioCategoryController::class)->names('portfoliocategory');
});
