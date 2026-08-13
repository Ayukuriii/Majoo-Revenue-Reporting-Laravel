<?php

use App\Http\Controllers\Api\Public\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);

Route::prefix('auth')->group(function (): void {
    require __DIR__.'/auth.php';
});

Route::middleware('auth:api')->group(function (): void {
    // Authenticated user routes
});
