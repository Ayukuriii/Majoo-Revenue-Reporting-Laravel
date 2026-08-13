<?php

use App\Http\Controllers\Api\User\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/reports/merchant', [ReportController::class, 'merchant']);
Route::get('/reports/outlet', [ReportController::class, 'outlet']);
