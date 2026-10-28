<?php

namespace App\Models\Concerns;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Per-language values for some text columns. The model lists them in
 * `protected array $translatable`; reading one of those attributes on a page
 * in another language returns the translation when there is one, otherwise
 * the default-language column. Templates need no change.
 *
 * Translations for a model type are loaded in one query per language and
 * request.
 */
trait HasTranslations
{
    /** @var array<string, array<string, array<int|string, array<string, string|null>>>> class => locale => id => field => value */
    private static array $translationCache = [];

    public static function bootHasTranslations(): void
    {
        static::deleted(function ($model) {
            Translation::where('translatable_type', $model->getMorphClass())->where('translatable_id', $model->getKey())->delete();
            self::$translationCache = [];
        });
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (! is_string($key) || ! in_array($key, $this->translatableFields(), true) || ! $this->exists) {
            return $value;
        }

        $locale = content_locale();

        if ($locale === '') {
            return $value;
        }

        $text = static::translationMap($locale)[$this->getKey()][$key] ?? null;

        if ($text === null || $text === '') {
            return $value;
        }

        return $this->hasCast($key) ? $this->castAttribute($key, $text) : $text;
    }

    /** Stored translation for the admin form (no fallback). */
    public function translationFor(string $locale, string $field): ?string
    {
        return static::translationMap($locale)[$this->getKey()][$field] ?? null;
    }

    /** Save the translations of one language; empty values remove the row. */
    public function setTranslations(string $locale, array $fields): void
    {
        foreach ($fields as $field => $value) {
            if (! in_array($field, $this->translatableFields(), true)) {
                continue;
            }

            $match = [
                'translatable_type' => $this->getMorphClass(),
                'translatable_id'   => $this->getKey(),
                'locale'            => $locale,
                'field'             => $field,
            ];

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if ($value === null || trim((string) $value) === '') {
                Translation::where($match)->delete();
            } else {
                Translation::updateOrCreate($match, ['value' => $value]);
            }
        }

        self::$translationCache = [];
    }

    /** @return array<int|string, array<string, string|null>> id => field => value */
    public static function translationMap(string $locale): array
    {
        $class = (new static)->getMorphClass();

        return self::$translationCache[$class][$locale] ??= Translation::where('translatable_type', $class)
            ->where('locale', $locale)
            ->get(['translatable_id', 'field', 'value'])
            ->groupBy('translatable_id')
            ->map(fn ($rows) => $rows->pluck('value', 'field')->all())
            ->all();
    }
}
