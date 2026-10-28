<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

/**
 * Tests run against `sb_website_test`, a copy of the site database (see
 * README → Tests), and every test is rolled back, so the copy stays as it was.
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'sb_website_test') {
            $this->fail('Tests must run on the sb_website_test database, never on the live one.');
        }
    }

    protected function admin(): User
    {
        $user = User::forceCreate([
            'name'     => 'Test admin',
            'email'    => 'test-admin@example.test',
            'password' => bcrypt('a-long-test-password'),
            'is_admin' => true,
        ]);

        $this->actingAs($user);

        return $user;
    }
}
