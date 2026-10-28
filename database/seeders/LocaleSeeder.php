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
            ['code' => 'fr_FR', 'url_prefix' => 'fr', 'label' => 'français', 'currency_code' => 'EUR', 'is_default' => false, 'sort_order' => 3],
        ];

        foreach ($locales as $locale) {
            Locale::updateOrCreate(['code' => $locale['code']], $locale);
        }
    }
}
