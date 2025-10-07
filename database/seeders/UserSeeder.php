<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@eqos.pl',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jan Kierownik',
            'email' => 'kierownik@eqos.pl',
            'password' => Hash::make('kierownik123'),
            'role' => 'kierownik',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Anna Lider',
            'email' => 'lider@eqos.pl',
            'password' => Hash::make('lider123'),
            'role' => 'lider',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Piotr Lider',
            'email' => 'lider2@eqos.pl',
            'password' => Hash::make('lider123'),
            'role' => 'lider',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Maria Księgowa',
            'email' => 'ksiegowy@eqos.pl',
            'password' => Hash::make('ksiegowy123'),
            'role' => 'ksiegowy',
            'email_verified_at' => now(),
        ]);
    }
}
