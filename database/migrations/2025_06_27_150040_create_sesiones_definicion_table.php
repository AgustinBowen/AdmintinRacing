<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesiones_definicion', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fecha_id');
            $table->string('tipo', [
                'entrenamiento_1',
                'entrenamiento_2', 
                'entrenamiento_3',
                'clasificacion',
                'serie_clasificatoria_1',
                'serie_clasificatoria_2',
                'serie_clasificatoria_3',
                'carrera_final'
            ]);
            $table->date('fecha_sesion')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Foreign key
            $table->foreign('fecha_id')->references('id')->on('fechas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_definicion');
    }
};