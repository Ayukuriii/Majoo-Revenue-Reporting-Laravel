<?php

use App\Http\Controllers\Api\Public\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);

Route::prefix('auth')->group(function (): void {
    // Auth routes (login) — Step 1+
});

Route::middleware('auth:api')->group(function (): void {
    // Authenticated user routes
});
