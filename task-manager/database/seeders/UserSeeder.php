<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcısı
        User::create([
            'name' => 'Admin Kullanıcı',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Normal kullanıcı
        User::create([
            'name' => 'Normal Kullanıcı',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // İlave 3 örnek normal kullanıcı
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Test Kullanıcı $i",
                'email' => "test$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);
        }
    }
}
