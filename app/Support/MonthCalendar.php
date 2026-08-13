<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

final class MonthCalendar
{
    /**
     * Every calendar date in the given month, in the application timezone.
     *
     * @return list<string> Dates as Y-m-d
     */
    public static function days(int $year, int $month): array
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Month must be between 1 and 12.');
        }

        $timezone = (string) config('app.timezone');
        $cursor = Carbon::create($year, $month, 1, 0, 0, 0, $timezone);
        $end = $cursor->copy()->endOfMonth()->startOfDay();

        $days = [];

        while ($cursor->lte($end)) {
            $days[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $days;
    }
}
