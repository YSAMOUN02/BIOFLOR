<?php

namespace Database\Seeders;

use App\Models\Retailer;
use Illuminate\Database\Seeder;

/**
 * "Where to buy" lists online shops and local pharmacies entered in
 * Admin → Where to buy. Nothing is seeded: this only removes the European
 * sample stores of earlier versions, and never touches stores added in the
 * admin.
 */
class RetailerSeeder extends Seeder
{
    /** Sample stores from earlier versions of this seeder. */
    public const SAMPLES = [
        'Partner Pharmacy Online', 'HealthShop EU', 'Farmacia Directa', 'PharmaBox', 'MagnaPharm Head Quarter',
        'Central Pharmacy', 'Pharmacie du Centre', 'Apoteka Zdravlje', 'Farmacia San Marco',
    ];

    public function run(): void
    {
        Retailer::whereIn('name', self::SAMPLES)->delete();
    }
}
