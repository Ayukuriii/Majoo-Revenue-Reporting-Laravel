<?php

use App\Models\Merchant;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * @param  array<string, mixed>  $userAttributes
 * @param  array<string, mixed>  $merchantAttributes
 */
function createMerchantUser(array $userAttributes = [], array $merchantAttributes = []): User
{
    $user = User::factory()->create($userAttributes);

    Merchant::factory()->create(array_merge([
        'user_id' => $user->id,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ], $merchantAttributes));

    return $user->fresh(['merchant']);
}

/**
 * @param  TestCase  $test
 */
function loginToken(object $test, User $user): string
{
    $response = $test->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();

    return $response->json('data.token');
}
