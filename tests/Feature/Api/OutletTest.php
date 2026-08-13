<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

describe('Outlets', function () {
    test('list without a token returns 401', function () {
        $response = $this->getJson('/api/outlets');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    });

    test('merchant one only receives outlets 1 and 3', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(1));

        $response = $this->getJson('/api/outlets?merchant_id=2');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids)->toEqual([1, 3]);
        expect($ids)->not->toContain(2);
    });

    test('merchant two never sees merchant one outlets', function () {
        $this->seed(DatabaseSeeder::class);

        actingAsMerchant($this, User::query()->findOrFail(2));

        $response = $this->getJson('/api/outlets');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids)->toEqual([2]);
        expect($ids)->not->toContain(1);
        expect($ids)->not->toContain(3);
    });
});
