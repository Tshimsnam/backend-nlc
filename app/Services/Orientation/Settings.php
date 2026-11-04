<?php

namespace App\Services\Orientation;

use Illuminate\Support\Facades\DB;

class Settings
{
    public static function get(string $key): mixed
    {
        return DB::table('orientation_settings')->where('key', $key)->value('value') ?? config('orientation.'.$key);
    }

    public static function url(string $path = '/espace-famille'): string
    {
        return rtrim(config('orientation.frontend_url'), '/').$path;
    }

    public static function paymentAvailable(): bool
    {
        return (bool) config('orientation.maxicash_enabled') || filled(self::get('manual_payment_instructions'));
    }
}
