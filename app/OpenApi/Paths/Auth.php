<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/auth/login',
    operationId: 'authLogin',
    description: 'Exchange email and password for a JWT. Merchant-less accounts fail after tenancy is enabled.',
    summary: 'Login',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest'),
    ),
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Bearer token issued',
            content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid credentials',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: 403,
            description: 'Account has no merchant (after tenancy step)',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation failed',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
#[OA\Post(
    path: '/auth/logout',
    operationId: 'authLogout',
    description: 'Invalidate the current JWT (blacklist).',
    summary: 'Logout',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Logged out',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
#[OA\Post(
    path: '/auth/refresh',
    operationId: 'authRefresh',
    description: 'Issue a new JWT from a still-valid refresh window.',
    summary: 'Refresh token',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'New bearer token',
            content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
#[OA\Get(
    path: '/auth/me',
    operationId: 'authMe',
    description: 'Current user. After tenancy, includes merchant_id and merchant_name.',
    summary: 'Current user',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Authenticated user',
            content: new OA\JsonContent(ref: '#/components/schemas/MeResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
final class Auth {}
