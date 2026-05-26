<?php

use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\ContactController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::get('/contacts', [ContactController::class, 'adminIndex']);
    Route::get('/contacts/{id}', [ContactController::class, 'adminShow']);
    Route::put('/contacts/{id}/seen-at', [ContactController::class, 'updateSeenAt']);
    Route::put('/contacts/{id}/admin-note', [ContactController::class, 'updateAdminNote']);
});
Route::post('/contact', [ContactController::class, 'store']);
