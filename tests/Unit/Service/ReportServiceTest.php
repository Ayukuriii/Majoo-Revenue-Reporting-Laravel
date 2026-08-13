<?php

use App\Exceptions\OutletNotOwnedException;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('merchant report fills missing days with zero omzet', function () {
    $user = createMerchantUser();
    $this->actingAs($user, 'api');

    $outlet = Outlet::factory()->create(['merchant_id' => $user->merchant->id]);

    Transaction::factory()->create([
        'merchant_id' => $user->merchant->id,
        'outlet_id' => $outlet->id,
        'bill_total' => '100.50',
        'created_at' => '2026-08-02 10:00:00',
        'updated_at' => '2026-08-02 10:00:00',
    ]);

    $paginator = app(ReportService::class)->merchantReport([
        'year' => 2026,
        'month' => 8,
        'page' => 1,
        'per_page' => 31,
    ]);

    $rows = collect($paginator->items());

    expect($paginator->total())->toBe(31);
    expect($rows)->toHaveCount(31);
    expect($rows->firstWhere('date', '2026-08-01')['omzet'])->toBe(0.0);
    expect($rows->firstWhere('date', '2026-08-02')['omzet'])->toBe(100.5);
    expect($rows->firstWhere('date', '2026-08-03')['omzet'])->toBe(0.0);
});

test('outlet report rejects an outlet that is not owned', function () {
    $owner = createMerchantUser();
    $other = createMerchantUser();
    $this->actingAs($owner, 'api');

    $foreign = Outlet::factory()->create(['merchant_id' => $other->merchant->id]);

    app(ReportService::class)->outletReport([
        'outlet_id' => $foreign->id,
        'year' => 2026,
        'month' => 8,
        'page' => 1,
        'per_page' => 10,
    ]);
})->throws(OutletNotOwnedException::class);
