<?php

namespace App\Models;

use Database\Factories\OutletFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    /** @use HasFactory<OutletFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'merchant_id',
        'outlet_name',
        'created_by',
        'updated_by',
    ];

    /**
     * @return BelongsTo<Merchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @param  Builder<Outlet>  $query
     * @return Builder<Outlet>
     */
    public function scopeForMerchant(Builder $query, int $merchantId): Builder
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * @param  Builder<Outlet>  $query
     * @return Builder<Outlet>
     */
    public function scopeForOutlet(Builder $query, int $outletId): Builder
    {
        return $query->whereKey($outletId);
    }
}
