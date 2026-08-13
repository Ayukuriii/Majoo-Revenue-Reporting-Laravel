<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'JWT-authenticated merchant revenue reporting API. All business endpoints live under `/api`. Internal auto-increment ids are used for merchants, outlets, and transactions (assignment exception vs UUID public_id).',
    title: 'Majoo Revenue Reporting API',
)]
#[OA\Server(url: '/api', description: 'Same-origin API prefix')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT from POST /auth/login. Send as Authorization: Bearer {token}.',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
final class OpenApiSpec {}
