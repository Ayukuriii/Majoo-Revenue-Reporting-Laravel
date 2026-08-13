<?php

namespace App\Http\Resources\Api\Outlet;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Outlet
 */
class OutletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $outlet = $this->resource;

        if (! $outlet instanceof Outlet) {
            return [];
        }

        return [
            'id' => $outlet->id,
            'outlet_name' => $outlet->outlet_name,
        ];
    }
}
