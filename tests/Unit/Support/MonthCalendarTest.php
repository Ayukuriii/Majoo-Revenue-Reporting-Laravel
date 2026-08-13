<?php

use App\Support\MonthCalendar;

test('november 2026 has 30 calendar days', function () {
    $days = MonthCalendar::days(2026, 11);

    expect($days)->toHaveCount(30);
    expect($days[0])->toBe('2026-11-01');
    expect($days[29])->toBe('2026-11-30');
});

test('august 2026 has 31 calendar days', function () {
    $days = MonthCalendar::days(2026, 8);

    expect($days)->toHaveCount(31);
    expect($days[0])->toBe('2026-08-01');
    expect($days[30])->toBe('2026-08-31');
});
