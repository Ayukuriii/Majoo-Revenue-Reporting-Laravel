<?php

use App\Exceptions\AccountHasNoMerchantException;
use App\Models\Outlet;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('currentMerchantId resolves from merchants.user_id not the request', function () {
    $user = createMerchantUser();
    $this->actingAs($user, 'api');

    request()->merge(['merchant_id' => 999]);

    expect(Tenant::currentMerchantId())->toBe($user->merchant->id);
});

test('currentMerchantId throws when the user has no merchant', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    Tenant::currentMerchantId();
})->throws(AccountHasNoMerchantException::class);

test('isOutletOwnedByMerchant is true only for the owning merchant', function () {
    $owner = createMerchantUser();
    $other = createMerchantUser();

    $owned = Outlet::factory()->create(['merchant_id' => $owner->merchant->id]);
    $foreign = Outlet::factory()->create(['merchant_id' => $other->merchant->id]);

    expect(Tenant::isOutletOwnedByMerchant($owned->id, $owner->merchant->id))->toBeTrue();
    expect(Tenant::isOutletOwnedByMerchant($foreign->id, $owner->merchant->id))->toBeFalse();
});
