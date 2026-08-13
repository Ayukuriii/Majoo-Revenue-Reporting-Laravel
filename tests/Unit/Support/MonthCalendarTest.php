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

test('february 2024 leap year has 29 calendar days', function () {
    $days = MonthCalendar::days(2024, 2);

    expect($days)->toHaveCount(29);
    expect($days[0])->toBe('2024-02-01');
    expect($days[28])->toBe('2024-02-29');
});

test('february 2025 has 28 calendar days', function () {
    $days = MonthCalendar::days(2025, 2);

    expect($days)->toHaveCount(28);
    expect($days[0])->toBe('2025-02-01');
    expect($days[27])->toBe('2025-02-28');
});
