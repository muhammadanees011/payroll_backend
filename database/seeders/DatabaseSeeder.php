<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '364878768768',
            'password' => '$2y$12$JpcFasaOmfRj4.qSrUvWsuFWcaRgQVsYIw7leQUV0uRxlBDgrDTBK' //password
        ]);
    }
}
