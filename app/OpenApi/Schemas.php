<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResponse',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'User data retrieved successfully'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ErrorMessage',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The email field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
            example: ['email' => ['The email field is required.']],
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'HealthData',
    required: ['status'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'ok'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'HealthResponse',
    required: ['data'],
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/HealthData'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'merchant1@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'TokenData',
    required: ['token', 'token_type', 'expires_in'],
    properties: [
        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
        new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginResponse',
    required: ['message', 'data'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
        new OA\Property(property: 'data', ref: '#/components/schemas/TokenData'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'UserData',
    required: ['email', 'name'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'merchant1@example.com'),
        new OA\Property(property: 'name', type: 'string', example: 'Merchant One'),
        new OA\Property(property: 'merchant_id', type: 'integer', example: 1),
        new OA\Property(property: 'merchant_name', type: 'string', example: 'merchant 1'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'MeResponse',
    required: ['data'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'User data retrieved successfully'),
        new OA\Property(property: 'data', ref: '#/components/schemas/UserData'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Outlet',
    required: ['id', 'outlet_name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'outlet_name', type: 'string', example: 'Outlet 1'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'OutletListResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Outlet'),
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'DailyOmzet',
    required: ['date', 'omzet'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-08-01'),
        new OA\Property(property: 'omzet', type: 'string', example: '4500.00', description: 'SUM(bill_total) for the calendar day; "0.00" when no transactions'),
        new OA\Property(property: 'merchant_name', type: 'string', example: 'merchant 1'),
        new OA\Property(property: 'outlet_name', type: 'string', example: 'Outlet 1'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 4),
        new OA\Property(property: 'per_page', type: 'integer', example: 10),
        new OA\Property(property: 'total', type: 'integer', example: 31),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginationLinks',
    properties: [
        new OA\Property(property: 'first', type: 'string', nullable: true),
        new OA\Property(property: 'last', type: 'string', nullable: true),
        new OA\Property(property: 'prev', type: 'string', nullable: true),
        new OA\Property(property: 'next', type: 'string', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginatedOmzetResponse',
    required: ['data', 'meta', 'links'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Report retrieved successfully'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/DailyOmzet'),
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    ],
    type: 'object',
)]
final class Schemas {}
