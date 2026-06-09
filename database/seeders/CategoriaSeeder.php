<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Campeonato;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear categoría "turismo pista 1100"
        $categoria = Categoria::create([
            'nombre' => 'Turismo Pista 1100',
            'descripcion' => 'Categoría principal de Turismo Pista 1100',
            'activa' => true,
        ]);

        // Crear una categoría adicional de ejemplo
        Categoria::create([
            'nombre' => 'Fórmula 1',
            'descripcion' => 'Ejemplo de otra categoría',
            'activa' => true,
        ]);

        // Asignar los campeonatos existentes a la primera categoría
        Campeonato::whereNull('categoria_id')->update([
            'categoria_id' => $categoria->id
        ]);
    }
}

