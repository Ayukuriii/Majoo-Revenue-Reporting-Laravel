<?php

namespace App\Http\Requests\Api\Report;

class OutletRevenueReportRequest extends MerchantRevenueReportRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'outlet_id' => ['required', 'integer'],
        ]);
    }
}
