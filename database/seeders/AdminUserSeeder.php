<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /** Creates the first admin only if it is missing, so re-seeding never resets a changed password. */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@sbglobal.test'],
            [
                'name'     => 'Site administrator',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );
    }
}
