<?php

test('swagger ui is available', function () {
    $this->artisan('l5-swagger:generate')->assertSuccessful();

    $response = $this->get('/api/documentation');

    $response->assertOk();
});

test('generated openapi contract lists health and auth paths', function () {
    $this->artisan('l5-swagger:generate')->assertSuccessful();

    $spec = json_decode(
        file_get_contents(storage_path('api-docs/api-docs.json')),
        true,
    );

    expect($spec['openapi'])->toStartWith('3.');
    expect($spec['paths'])->toHaveKeys([
        '/health',
        '/auth/login',
        '/auth/logout',
        '/auth/refresh',
        '/auth/me',
        '/outlets',
        '/reports/merchant',
        '/reports/outlet',
    ]);
});
