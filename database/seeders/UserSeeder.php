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
            ['Admin Demo',    'admin@test.com',    'administracion', '+54 2302 100001'],
            ['Gestor Demo',   'gestor@test.com',   'gestion',        '+54 2302 100002'],
            ['Revisor Demo',  'revisor@test.com',  'revision',       '+54 2302 100003'],
            ['Consulta Demo', 'consulta@test.com', 'consulta',       '+54 2302 100004'],
            ['Decano Demo',   'decano@test.com',   'decano',         '+54 2302 100005'],
        ];

        foreach ($usuarios as [$name, $email, $rol, $tel]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'telefono' => $tel, 'password' => Hash::make('123456'), 'rol' => $rol]
            );
        }
    }
}
