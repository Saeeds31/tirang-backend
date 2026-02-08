<?php

use Illuminate\Support\Facades\Route;
use Modules\Employer\Http\Controllers\EmployerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('employers', EmployerController::class)->names('employer');
});
