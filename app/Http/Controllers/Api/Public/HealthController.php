<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Application health check.
     */
    public function index(): JsonResponse
    {
        return respondWithData(data: ['status' => 'ok']);
    }
}
