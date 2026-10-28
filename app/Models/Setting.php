<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/** Key/value site settings, read through the setting() helper. */
class Setting extends Model
{
    public const TYPES = ['text', 'textarea', 'richtext', 'image', 'url', 'email', 'boolean', 'number'];

    protected $guarded = [];

    /** @var array<string, string|null>|null loaded once per request */
    private static ?array $cache = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$cache === null) {
            self::$cache = rescue(
                fn () => Schema::hasTable('settings') ? static::query()->pluck('value', 'key')->all() : [],
                [],
                false,
            );
        }

        // A translated value is stored under "key:km" and wins on pages in that language.
        $locale = content_locale();

        if ($locale !== '' && ($translated = self::$cache[$key.':'.$locale] ?? null) !== null && $translated !== '') {
            return $translated;
        }

        $value = self::$cache[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    /** Value of a setting in one language, without fallback (admin forms). */
    public static function translated(string $key, string $locale): ?string
    {
        return static::where('key', $key.':'.$locale)->value('value');
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
