<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Outlet\OutletResource;
use App\Services\OutletService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OutletController extends Controller
{
    public function __construct(private readonly OutletService $outletService) {}

    /**
     * List outlets for the authenticated merchant.
     */
    public function index(): JsonResponse
    {
        try {
            $outlets = $this->outletService->listForCurrentMerchant();

            return respondWithData(
                data: OutletResource::collection($outlets)->resolve(),
                message: 'Outlet data retrieved successfully',
            );
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = is_int($code) && $code >= 400 && $code < 600
                ? $code
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            return respondError(error: $e->getMessage(), statusCode: $statusCode);
        }
    }
}
