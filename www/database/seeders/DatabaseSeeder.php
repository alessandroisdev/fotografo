<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fotógrafo Primário (Admin)
        User::firstOrCreate(
            ['email' => 'admin@fotografo.io'],
            [
                'name' => 'Fotógrafo Administrador',
                'uuid' => Str::uuid()->toString(),
                'role' => 'admin',
                'password' => bcrypt('password123'),
                'document' => '000.000.000-00'
            ]
        );

        // 2. Cliente de Teste Curioso
        User::firstOrCreate(
            ['email' => 'cliente@teste.com'],
            [
                'name' => 'João Cliente da Silva',
                'uuid' => Str::uuid()->toString(),
                'role' => 'client',
                'password' => bcrypt('cliente123'),
                'document' => '111.222.333-44'
            ]
        );
    }
}
