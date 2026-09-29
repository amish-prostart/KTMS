<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /*
     * Note: the WithoutModelEvents trait is deliberately not used here. The
     * Tournament model generates its slug from a saving hook, and muting model
     * events would leave the seeded tournaments without one.
     */

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Match Operator',
            'email' => 'operator@ktms.test',
        ]);

        $this->call(KabaddiTournamentSeeder::class);
    }
}
