<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/health',
    operationId: 'healthCheck',
    description: 'Unauthenticated liveness check. Does not touch the database.',
    summary: 'Health check',
    tags: ['Public'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Service is up',
            content: new OA\JsonContent(ref: '#/components/schemas/HealthResponse'),
        ),
    ],
)]
final class Health {}
