<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LibrarySetting
{
    private const CACHE_KEY = 'library_system_settings';

    private static ?array $loadedSettings = null;

    public static function lateFeePerDay(): int
    {
        return (int) static::get('late_fee_per_day', config('perpustakaan.late_price', 2000));
    }

    public static function bookReplacementPrice(): int
    {
        return (int) static::get('book_replacement_price', config('perpustakaan.book_price', 50000));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::all();

        return $settings[$key] ?? $default;
    }

    public static function setMany(array $settings): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        foreach ($settings as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value],
            );
        }

        static::forgetCache();
    }

    public static function forgetCache(): void
    {
        static::$loadedSettings = null;
        Cache::forget(self::CACHE_KEY);
    }

    public static function all(): array
    {
        if (static::$loadedSettings !== null) {
            return static::$loadedSettings;
        }

        if (! Schema::hasTable('system_settings')) {
            return static::$loadedSettings = [];
        }

        static::$loadedSettings = Cache::rememberForever(self::CACHE_KEY, function () {
            return SystemSetting::query()
                ->pluck('value', 'key')
                ->map(fn ($value) => is_numeric($value) ? (int) $value : $value)
                ->all();
        });

        return static::$loadedSettings;
    }
}
