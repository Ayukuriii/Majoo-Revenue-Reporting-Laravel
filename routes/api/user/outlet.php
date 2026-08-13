<?php

use App\Http\Controllers\Api\User\OutletController;
use Illuminate\Support\Facades\Route;

Route::get('/outlets', [OutletController::class, 'index']);
