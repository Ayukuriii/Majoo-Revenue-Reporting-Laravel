<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/outlets',
    operationId: 'listOutlets',
    description: 'Outlets for the JWT merchant only. Never accepts merchant_id from the client.',
    summary: 'List merchant outlets',
    security: [['bearerAuth' => []]],
    tags: ['Outlets'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Tenant outlets',
            content: new OA\JsonContent(ref: '#/components/schemas/OutletListResponse'),
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
final class Outlets {}
