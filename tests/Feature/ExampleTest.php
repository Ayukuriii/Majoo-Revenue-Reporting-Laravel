<?php

test('the application health endpoint returns ok', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();
    $response->assertJson([
        'data' => [
            'status' => 'ok',
        ],
    ]);
});
