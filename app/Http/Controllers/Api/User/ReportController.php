<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Report\MerchantRevenueReportRequest;
use App\Http\Requests\Api\Report\OutletRevenueReportRequest;
use App\Http\Resources\Api\Report\DailyOmzetResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * Monthly daily omzet for the authenticated merchant.
     */
    public function merchant(MerchantRevenueReportRequest $request): JsonResponse
    {
        try {
            $paginator = $this->reportService->merchantReport($request->validated());

            return respondWithPaginatedData(
                collection: DailyOmzetResource::collection($paginator),
                message: 'Report retrieved successfully',
            );
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = is_int($code) && $code >= 400 && $code < 600
                ? $code
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            return respondError(error: $e->getMessage(), statusCode: $statusCode);
        }
    }

    /**
     * Monthly daily omzet for one outlet owned by the authenticated merchant.
     */
    public function outlet(OutletRevenueReportRequest $request): JsonResponse
    {
        try {
            $paginator = $this->reportService->outletReport($request->validated());

            return respondWithPaginatedData(
                collection: DailyOmzetResource::collection($paginator),
                message: 'Report retrieved successfully',
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
