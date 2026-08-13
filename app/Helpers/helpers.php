<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('storageAsset')) {
    /**
     * Public disk URL for a stored path.
     */
    function storageAsset(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('adminMediaUrl')) {
    /**
     * Admin upload URL using the configured filesystem disk.
     */
    function adminMediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $disk = config('filesystems.default', 'local');

        return Storage::disk($disk)->url($path);
    }
}

if (! function_exists('carbonParse')) {
    /**
     * Parse a date value into Carbon.
     */
    function carbonParse(mixed $date): Carbon
    {
        return Carbon::parse($date);
    }
}

if (! function_exists('parseToTimezone')) {
    /**
     * Convert a date to the given timezone (default Asia/Jakarta).
     */
    function parseToTimezone(mixed $date, ?string $timezone = null): Carbon
    {
        return Carbon::parse($date)->timezone($timezone ?? 'Asia/Jakarta');
    }
}
