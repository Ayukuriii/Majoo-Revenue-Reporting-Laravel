<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class AssignmentDataSeeder extends Seeder
{
    /**
     * Insert the programming-test merchant, outlet, and transaction rows.
     *
     * Transaction created_at values are stored as naive Asia/Jakarta local times
     * (app timezone) so DATE() grouping matches the assignment calendar days.
     */
    public function run(): void
    {
        $now = now();

        Merchant::query()->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'merchant_name' => 'merchant 1',
                'created_at' => $now,
                'created_by' => 1,
                'updated_at' => $now,
                'updated_by' => 1,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'merchant_name' => 'Merchant 2',
                'created_at' => $now,
                'created_by' => 2,
                'updated_at' => $now,
                'updated_by' => 2,
            ],
        ]);

        Outlet::query()->insert([
            [
                'id' => 1,
                'merchant_id' => 1,
                'outlet_name' => 'Outlet 1',
                'created_at' => $now,
                'created_by' => 1,
                'updated_at' => $now,
                'updated_by' => 1,
            ],
            [
                'id' => 2,
                'merchant_id' => 2,
                'outlet_name' => 'Outlet 1',
                'created_at' => $now,
                'created_by' => 2,
                'updated_at' => $now,
                'updated_by' => 2,
            ],
            [
                'id' => 3,
                'merchant_id' => 1,
                'outlet_name' => 'Outlet 2',
                'created_at' => $now,
                'created_by' => 1,
                'updated_at' => $now,
                'updated_by' => 1,
            ],
        ]);

        Transaction::query()->insert($this->transactions());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function transactions(): array
    {
        $rows = [
            [1, 1, 1, '2000.00', '2026-08-01 12:30:04', 1],
            [2, 1, 1, '2500.00', '2026-08-01 17:20:14', 1],
            [3, 1, 1, '4000.00', '2026-08-02 12:30:04', 1],
            [4, 1, 1, '1000.00', '2026-08-04 12:30:04', 1],
            [5, 1, 1, '7000.00', '2026-08-05 16:59:30', 1],
            [6, 1, 3, '2000.00', '2026-08-02 18:30:04', 1],
            [7, 1, 3, '2500.00', '2026-08-03 17:20:14', 1],
            [8, 1, 3, '4000.00', '2026-08-04 12:30:04', 1],
            [9, 1, 3, '1000.00', '2026-08-04 12:31:04', 1],
            [10, 1, 3, '7000.00', '2026-08-05 16:59:30', 1],
            [11, 2, 2, '2000.00', '2026-08-01 18:30:04', 2],
            [12, 2, 2, '2500.00', '2026-08-02 17:20:14', 2],
            [13, 2, 2, '4000.00', '2026-08-03 12:30:04', 2],
            [14, 2, 2, '1000.00', '2026-08-04 12:31:04', 2],
            [15, 2, 2, '7000.00', '2026-08-05 16:59:30', 2],
            [16, 2, 2, '2000.00', '2026-08-05 18:30:04', 2],
            [17, 2, 2, '2500.00', '2026-08-06 17:20:14', 2],
            [18, 2, 2, '4000.00', '2026-08-07 12:30:04', 2],
            [19, 2, 2, '1000.00', '2026-08-08 12:31:04', 2],
            [20, 2, 2, '7000.00', '2026-08-09 16:59:30', 2],
            [21, 2, 2, '1000.00', '2026-08-10 12:31:04', 2],
            [22, 2, 2, '7000.00', '2026-08-11 16:59:30', 2],
        ];

        return array_map(static function (array $row): array {
            [$id, $merchantId, $outletId, $billTotal, $occurredAt, $actorId] = $row;

            return [
                'id' => $id,
                'merchant_id' => $merchantId,
                'outlet_id' => $outletId,
                'bill_total' => $billTotal,
                'created_at' => $occurredAt,
                'created_by' => $actorId,
                'updated_at' => $occurredAt,
                'updated_by' => $actorId,
            ];
        }, $rows);
    }
}
