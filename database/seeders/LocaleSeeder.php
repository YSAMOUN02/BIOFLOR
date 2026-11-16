<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        $locales = [
            ['code' => 'en_US', 'url_prefix' => 'en', 'label' => 'English',  'currency_code' => 'USD', 'is_default' => true,  'sort_order' => 1],
            ['code' => 'km_KH', 'url_prefix' => 'km', 'label' => 'ខ្មែរ',    'currency_code' => 'KHR', 'is_default' => false, 'sort_order' => 2],
        ];

        // French was removed: drop its language row and any stored French text.
        Locale::where('url_prefix', 'fr')->delete();
        \App\Models\Translation::where('locale', 'fr')->delete();

        foreach ($locales as $locale) {
            Locale::updateOrCreate(['code' => $locale['code']], $locale);
        }
    }
}
