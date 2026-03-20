<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sesiones_definicion', function (Blueprint $table) {
            $table->string('tipo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesiones_definicion', function (Blueprint $table) {
            $table->enum('tipo', [
                'entrenamiento_1',
                'entrenamiento_2',
                'entrenamiento_3',
                'clasificacion',
                'serie_clasificatoria_1',
                'serie_clasificatoria_2',
                'serie_clasificatoria_3',
                'carrera_final'
            ])->change();
        });
    }
};
