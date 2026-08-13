<?php

namespace App\Support;

use App\Exceptions\AccountHasNoMerchantException;
use App\Models\Merchant;
use App\Models\Outlet;
use App\Models\User;

final class Tenant
{
    /**
     * Resolve the authenticated user's merchant id from merchants.user_id.
     *
     * Never reads merchant_id from the request or JWT claims.
     *
     * @throws AccountHasNoMerchantException
     */
    public static function currentMerchantId(): int
    {
        $user = auth('api')->user();

        if (! $user instanceof User) {
            throw new AccountHasNoMerchantException;
        }

        $merchantId = Merchant::query()
            ->where('user_id', $user->id)
            ->value('id');

        if (! is_int($merchantId) && ! is_numeric($merchantId)) {
            throw new AccountHasNoMerchantException;
        }

        return (int) $merchantId;
    }

    /**
     * Whether the outlet belongs to the given merchant.
     */
    public static function isOutletOwnedByMerchant(int $outletId, int $merchantId): bool
    {
        return Outlet::query()
            ->forMerchant($merchantId)
            ->whereKey($outletId)
            ->exists();
    }
}
