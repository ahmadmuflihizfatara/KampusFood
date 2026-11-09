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
     *
     * Local test accounts (username / password "password"):
     * - admin (admin kantin)
     * - mahasiswa (mahasiswa)
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Kantin',
            'username' => 'admin',
            'email' => 'admin@kampusfood.test',
        ]);

        User::factory()->create([
            'name' => 'Budi Mahasiswa',
            'username' => 'mahasiswa',
            'email' => 'mahasiswa@kampusfood.test',
        ]);

        User::factory()->admin()->create([
            'name' => 'Ahmad Muflih',
            'username' => 'ahmad',
            'email' => 'ahmad@kampusfood.test',
            'password' => 'yws5wBeKUyJK',
        ]);

        $this->call(MenuSeeder::class);
    }
}
