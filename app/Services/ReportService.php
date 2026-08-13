<?php

namespace App\Services;

use App\Exceptions\AccountHasNoMerchantException;
use App\Exceptions\OutletNotOwnedException;
use App\Models\Merchant;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Support\MonthCalendar;
use App\Support\Tenant;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Daily omzet for the authenticated merchant.
     *
     * @param  array<string, mixed>  $data
     * @return LengthAwarePaginator<int, array{date: string, omzet: float, merchant_name: string, outlet_name?: string}>
     *
     * @throws AccountHasNoMerchantException
     */
    public function merchantReport(array $data): LengthAwarePaginator
    {
        $merchantId = Tenant::currentMerchantId();
        $merchant = Merchant::query()->findOrFail($merchantId);

        return $this->buildDailyReport(
            merchantId: $merchantId,
            merchantName: (string) $merchant->merchant_name,
            year: (int) $data['year'],
            month: (int) $data['month'],
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 10),
            outletId: null,
            outletName: null,
        );
    }

    /**
     * Daily omzet for one outlet owned by the authenticated merchant.
     *
     * @param  array<string, mixed>  $data
     * @return LengthAwarePaginator<int, array{date: string, omzet: float, merchant_name: string, outlet_name?: string}>
     *
     * @throws OutletNotOwnedException
     * @throws AccountHasNoMerchantException
     */
    public function outletReport(array $data): LengthAwarePaginator
    {
        $merchantId = Tenant::currentMerchantId();
        $merchant = Merchant::query()->findOrFail($merchantId);
        $outletId = (int) $data['outlet_id'];

        if (! Tenant::isOutletOwnedByMerchant($outletId, $merchantId)) {
            throw new OutletNotOwnedException;
        }

        $outlet = Outlet::query()->findOrFail($outletId);

        return $this->buildDailyReport(
            merchantId: $merchantId,
            merchantName: (string) $merchant->merchant_name,
            year: (int) $data['year'],
            month: (int) $data['month'],
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 10),
            outletId: $outletId,
            outletName: (string) $outlet->outlet_name,
        );
    }

    /**
     * @return LengthAwarePaginator<int, array{date: string, omzet: float, merchant_name: string, outlet_name?: string}>
     */
    private function buildDailyReport(
        int $merchantId,
        string $merchantName,
        int $year,
        int $month,
        int $page,
        int $perPage,
        ?int $outletId,
        ?string $outletName,
    ): LengthAwarePaginator {
        $timezone = (string) config('app.timezone');
        $start = Carbon::create($year, $month, 1, 0, 0, 0, $timezone);
        $end = $start->copy()->endOfMonth();

        $omzetByDate = $this->sumOmzetByDate($merchantId, $start, $end, $outletId);

        $days = collect(MonthCalendar::days($year, $month))->map(function (string $date) use ($omzetByDate, $merchantName, $outletName): array {
            $row = [
                'date' => $date,
                'omzet' => (float) ($omzetByDate[$date] ?? 0),
                'merchant_name' => $merchantName,
            ];

            if ($outletName !== null) {
                $row['outlet_name'] = $outletName;
            }

            return $row;
        });

        return $this->paginateDays($days, $page, $perPage);
    }

    /**
     * @return Collection<string, mixed>
     */
    private function sumOmzetByDate(int $merchantId, Carbon $start, Carbon $end, ?int $outletId): Collection
    {
        return Transaction::query()
            ->forMerchant($merchantId)
            ->when($outletId !== null, fn ($query) => $query->forOutlet((int) $outletId))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->selectRaw('DATE(created_at) as report_date, SUM(bill_total) as omzet')
            ->groupByRaw('DATE(created_at)')
            ->pluck('omzet', 'report_date');
    }

    /**
     * @param  Collection<int, array{date: string, omzet: float, merchant_name: string, outlet_name?: string}>  $days
     * @return LengthAwarePaginator<int, array{date: string, omzet: float, merchant_name: string, outlet_name?: string}>
     */
    private function paginateDays(Collection $days, int $page, int $perPage): LengthAwarePaginator
    {
        $pageItems = $days->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $days->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }
}
