<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bhima.go.id'],
            [
                'name' => 'Administrator BHIMA',
                'password' => 'password12345',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'verifikator@bhima.go.id'],
            [
                'name' => 'Petugas Verifikator BHIMA',
                'password' => 'password12345',
                'role' => 'verifikator',
                'is_active' => true,
            ]
        );

        $this->call(LegalKnowledgeSeeder::class);
    }
}
