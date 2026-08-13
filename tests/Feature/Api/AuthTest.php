<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

describe('Authentication', function () {
    test('login validation fails with an invalid body', function () {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['email', 'password']);
    });

    test('login returns 401 for unknown credentials', function () {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Invalid credentials',
        ]);
    });

    test('login returns 401 for a wrong password', function () {
        $user = createMerchantUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'not-the-password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Invalid credentials',
        ]);
    });

    test('login returns 403 when the account has no merchant', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Account has no merchant',
        ]);
    });

    test('login returns a bearer token', function () {
        $user = createMerchantUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'data' => ['token', 'token_type', 'expires_in'],
        ]);
        $response->assertJsonPath('data.token_type', 'bearer');
        expect($response->json('data.expires_in'))->toBeInt()->toBeGreaterThan(0);
    });

    test('me without a token returns 401', function () {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    });

    test('me returns the authenticated user without a password', function () {
        $user = createMerchantUser([
            'name' => 'Merchant One',
            'email' => 'merchant1@example.com',
        ], [
            'merchant_name' => 'merchant 1',
        ]);

        $token = loginToken($this, $user);

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.email', 'merchant1@example.com');
        $response->assertJsonPath('data.name', 'Merchant One');
        $response->assertJsonPath('data.merchant_id', $user->merchant->id);
        $response->assertJsonPath('data.merchant_name', 'merchant 1');
        $response->assertJsonMissingPath('data.password');
    });

    test('logout invalidates the token', function () {
        $user = createMerchantUser();
        $token = loginToken($this, $user);

        $logout = $this->withToken($token)->postJson('/api/auth/logout');
        $logout->assertOk();
        $logout->assertJsonStructure(['message']);

        $me = $this->withToken($token)->getJson('/api/auth/me');
        $me->assertStatus(Response::HTTP_UNAUTHORIZED);
    });

    test('refresh returns a new bearer token', function () {
        $user = createMerchantUser();
        $token = loginToken($this, $user);

        $response = $this->withToken($token)->postJson('/api/auth/refresh');

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'data' => ['token', 'token_type', 'expires_in'],
        ]);
        $response->assertJsonPath('data.token_type', 'bearer');
        expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    });
});
