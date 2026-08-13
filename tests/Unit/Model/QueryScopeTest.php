<?php

use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('outlet forMerchant and forOutlet scopes isolate rows', function () {
    $owner = createMerchantUser();
    $other = createMerchantUser();

    $ownedA = Outlet::factory()->create(['merchant_id' => $owner->merchant->id]);
    $ownedB = Outlet::factory()->create(['merchant_id' => $owner->merchant->id]);
    $foreign = Outlet::factory()->create(['merchant_id' => $other->merchant->id]);

    $ids = Outlet::query()->forMerchant($owner->merchant->id)->orderBy('id')->pluck('id')->all();

    expect($ids)->toEqual([$ownedA->id, $ownedB->id]);
    expect($ids)->not->toContain($foreign->id);

    expect(Outlet::query()->forOutlet($ownedA->id)->value('id'))->toBe($ownedA->id);
});

test('transaction forMerchant and forOutlet scopes isolate rows', function () {
    $owner = createMerchantUser();
    $other = createMerchantUser();

    $ownedOutlet = Outlet::factory()->create(['merchant_id' => $owner->merchant->id]);
    $foreignOutlet = Outlet::factory()->create(['merchant_id' => $other->merchant->id]);

    $owned = Transaction::factory()->create([
        'merchant_id' => $owner->merchant->id,
        'outlet_id' => $ownedOutlet->id,
    ]);
    Transaction::factory()->create([
        'merchant_id' => $other->merchant->id,
        'outlet_id' => $foreignOutlet->id,
    ]);

    expect(Transaction::query()->forMerchant($owner->merchant->id)->pluck('id')->all())
        ->toEqual([$owned->id]);

    expect(Transaction::query()->forOutlet($ownedOutlet->id)->pluck('id')->all())
        ->toEqual([$owned->id]);
});
