<?php

namespace App\Services;

use App\Models\Outlet;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Collection;

class OutletService
{
    /**
     * Outlets owned by the authenticated merchant only.
     *
     * @return Collection<int, Outlet>
     */
    public function listForCurrentMerchant(): Collection
    {
        return Outlet::query()
            ->forMerchant(Tenant::currentMerchantId())
            ->orderBy('id')
            ->get();
    }
}
