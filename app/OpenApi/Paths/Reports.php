<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/reports/merchant',
    operationId: 'merchantRevenueReport',
    description: 'Daily omzet for the token merchant. Every calendar day in the month is returned; missing days are "0.00". merchant_id query params are ignored. Paginated over days, not raw transactions.',
    summary: 'Merchant monthly revenue',
    security: [['bearerAuth' => []]],
    tags: ['Reports'],
    parameters: [
        new OA\QueryParameter(
            name: 'year',
            description: 'Calendar year',
            required: true,
            schema: new OA\Schema(type: 'integer', example: 2026),
        ),
        new OA\QueryParameter(
            name: 'month',
            description: 'Calendar month 1–12',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 11),
        ),
        new OA\QueryParameter(
            name: 'page',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
        new OA\QueryParameter(
            name: 'per_page',
            description: 'Default 10, max 31',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 31, example: 10),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated daily omzet',
            content: new OA\JsonContent(ref: '#/components/schemas/PaginatedOmzetResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation failed',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
#[OA\Get(
    path: '/reports/outlet',
    operationId: 'outletRevenueReport',
    description: 'Daily omzet for one outlet owned by the token merchant. Cross-tenant outlet_id returns 403. Every calendar day is present; missing days are "0.00".',
    summary: 'Outlet monthly revenue',
    security: [['bearerAuth' => []]],
    tags: ['Reports'],
    parameters: [
        new OA\QueryParameter(
            name: 'outlet_id',
            description: 'Must belong to the authenticated merchant',
            required: true,
            schema: new OA\Schema(type: 'integer', example: 1),
        ),
        new OA\QueryParameter(
            name: 'year',
            required: true,
            schema: new OA\Schema(type: 'integer', example: 2026),
        ),
        new OA\QueryParameter(
            name: 'month',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 8),
        ),
        new OA\QueryParameter(
            name: 'page',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
        new OA\QueryParameter(
            name: 'per_page',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 31, example: 10),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated daily omzet',
            content: new OA\JsonContent(ref: '#/components/schemas/PaginatedOmzetResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: 403,
            description: 'Outlet does not belong to the token merchant',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation failed',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
final class Reports {}
