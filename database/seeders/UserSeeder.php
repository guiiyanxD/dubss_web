<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'ci' => '1234567',
            'email' => 'admin@dubss.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77712345',
            'rol' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Operadores
        User::create([
            'nombre' => 'María',
            'apellido' => 'González',
            'ci' => '2345678',
            'email' => 'maria.gonzalez@dubss.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77723456',
            'rol' => 'operador',
            'email_verified_at' => now(),
        ]);

        User::create([
            'nombre' => 'Carlos',
            'apellido' => 'Rodríguez',
            'ci' => '3456789',
            'email' => 'carlos.rodriguez@dubss.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77734567',
            'rol' => 'operador',
            'email_verified_at' => now(),
        ]);

        // Estudiantes de prueba
        User::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'ci' => '4567890',
            'email' => 'juan.perez@estudiante.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77745678',
            'rol' => 'estudiante',
            'email_verified_at' => now(),
        ]);

        User::create([
            'nombre' => 'Ana',
            'apellido' => 'Martínez',
            'ci' => '5678901',
            'email' => 'ana.martinez@estudiante.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77756789',
            'rol' => 'estudiante',
            'email_verified_at' => now(),
        ]);

        User::create([
            'nombre' => 'Pedro',
            'apellido' => 'López',
            'ci' => '6789012',
            'email' => 'pedro.lopez@estudiante.edu.bo',
            'password' => Hash::make('password'),
            'telefono' => '77767890',
            'rol' => 'estudiante',
            'email_verified_at' => now(),
        ]);
    }
}
