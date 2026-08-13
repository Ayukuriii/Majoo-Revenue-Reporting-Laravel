<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

describe('Revenue reports', function () {
    test('merchant report without a token returns 401', function () {
        $response = $this->getJson('/api/reports/merchant?year=2026&month=11');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    });

    test('merchant report validation fails without year and month', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/reports/merchant');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['year', 'month']);
    });

    test('merchant report validation fails for an invalid month', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/reports/merchant?year=2026&month=13');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['month']);
    });

    test('merchant november 2026 is thirty zero days and paginates', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $firstPage = $this->getJson('/api/reports/merchant?year=2026&month=11');

        $firstPage->assertOk();
        $firstPage->assertJsonPath('meta.total', 30);
        $firstPage->assertJsonPath('meta.current_page', 1);
        $firstPage->assertJsonPath('meta.per_page', 10);
        $firstPage->assertJsonPath('meta.last_page', 3);

        $data = $firstPage->json('data');
        expect($data)->toHaveCount(10);
        expect($data[0]['date'])->toBe('2026-11-01');
        expect(collect($data)->every(fn (array $row): bool => $row['omzet'] === '0.00'))->toBeTrue();
        expect($data[0]['merchant_name'])->toBe('merchant 1');

        $fullMonth = $this->getJson('/api/reports/merchant?year=2026&month=11&per_page=30');

        $fullMonth->assertOk();
        $fullMonth->assertJsonPath('meta.total', 30);
        expect($fullMonth->json('data'))->toHaveCount(30);
        expect(collect($fullMonth->json('data'))->every(
            fn (array $row): bool => $row['omzet'] === '0.00'
        ))->toBeTrue();
    });

    test('merchant august 2026 day one is 4500 and ignores merchant_id query', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/reports/merchant?year=2026&month=8&per_page=5&merchant_id=2');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 31);
        $response->assertJsonPath('data.0.date', '2026-08-01');
        $response->assertJsonPath('data.0.omzet', '4500.00');
        $response->assertJsonPath('data.1.omzet', '6000.00');
        $response->assertJsonPath('data.2.omzet', '2500.00');
        $response->assertJsonPath('data.3.omzet', '6000.00');
        $response->assertJsonPath('data.4.omzet', '14000.00');
        $response->assertJsonPath('data.0.merchant_name', 'merchant 1');

        $fullMonth = $this->getJson('/api/reports/merchant?year=2026&month=8&per_page=31');
        $rows = collect($fullMonth->json('data'));

        $fullMonth->assertOk();
        expect($rows->firstWhere('date', '2026-08-06')['omzet'])->toBe('0.00');
    });

    test('outlet one august day three is zero', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/reports/outlet?outlet_id=1&year=2026&month=8&per_page=5');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 31);
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.last_page', 7);
        $response->assertJsonPath('data.0.date', '2026-08-01');
        $response->assertJsonPath('data.0.omzet', '4500.00');
        $response->assertJsonPath('data.1.omzet', '4000.00');
        $response->assertJsonPath('data.2.date', '2026-08-03');
        $response->assertJsonPath('data.2.omzet', '0.00');
        $response->assertJsonPath('data.3.omzet', '1000.00');
        $response->assertJsonPath('data.4.omzet', '7000.00');
        $response->assertJsonPath('data.0.outlet_name', 'Outlet 1');
    });

    test('user one cannot request outlet two', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/reports/outlet?outlet_id=2&year=2026&month=8');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user two cannot request outlet one', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(2));

        $response = $this->getJson('/api/reports/outlet?outlet_id=1&year=2026&month=8');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
});
