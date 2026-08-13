<?php

namespace App\Http\Resources\Api\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyOmzetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $row = is_array($this->resource) ? $this->resource : [];

        $data = [
            'date' => $row['date'] ?? null,
            'omzet' => number_format((float) ($row['omzet'] ?? 0), 2, '.', ''),
            'merchant_name' => $row['merchant_name'] ?? null,
        ];

        if (array_key_exists('outlet_name', $row)) {
            $data['outlet_name'] = $row['outlet_name'];
        }

        return $data;
    }
}
