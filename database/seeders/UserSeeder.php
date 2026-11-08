<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $usuarios = [
            ['Admin Demo', 'admin@test.com', 'administracion'],
            ['Gestor Demo', 'gestor@test.com', 'gestion'],
            ['Revisor Demo', 'revisor@test.com', 'revision'],
            ['Consulta Demo', 'consulta@test.com', 'consulta'],
        ];

        foreach ($usuarios as [$name, $email, $rol]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('123456'), 'rol' => $rol]
            );
        }
    }
}
