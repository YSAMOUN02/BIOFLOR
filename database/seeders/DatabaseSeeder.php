<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            MediaSeeder::class,       // images and video from the Product folder
            SettingsSeeder::class,    // brand() is read by the seeders below
            LocaleSeeder::class,
            CatalogSeeder::class,     // attribute dictionary, product types, categories
            ProductSeeder::class,
            ArticleSeeder::class,
            NavSeeder::class,
            ContentSeeder::class,
            PageSectionSeeder::class, // home, range, product page, hubs, about, Q&A
            RetailerSeeder::class,
            TranslationSeeder::class, // Khmer text, matched to the records above
        ]);
    }
}
