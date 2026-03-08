<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
  public function run()
{
    $users = [
        [
            'name' => 'Chris Admin',
            'email' => 'chris@whatsapp.com',  // Cambiado de @gmail.com a @whatsapp.com
            'password' => Hash::make('12345678'),
            'is_online' => false,
        ],
        [
            'name' => 'Ana García',
            'email' => 'ana@example.com',
            'password' => Hash::make('12345678'),
            'is_online' => false,
        ],
        [
            'name' => 'Carlos López',
            'email' => 'carlos@example.com',
            'password' => Hash::make('12345678'),
            'is_online' => false,
        ],
        [
            'name' => 'María Rodríguez',
            'email' => 'maria@example.com',
            'password' => Hash::make('12345678'),
            'is_online' => false,
        ],
        [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => Hash::make('12345678'),
            'is_online' => false,
        ],
    ];

    foreach ($users as $user) {
        // Solo crear si el email NO existe
        if (!User::where('email', $user['email'])->exists()) {
            User::create($user);
            $this->command->info("Usuario {$user['email']} creado!");
        } else {
            $this->command->warn("Usuario {$user['email']} ya existe, omitiendo...");
        }
    }

}
}