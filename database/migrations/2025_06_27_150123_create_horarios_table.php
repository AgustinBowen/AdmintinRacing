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
        Schema::create('horarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sesion_id');
            $table->uuid('fecha_id');
            $table->timestamp('horario');
            $table->string('duracion')->nullable(); // Laravel maneja intervalos como strings
            $table->text('observaciones')->nullable(); // Corregido el typo "obserevaciones"
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Clave primaria compuesta
            $table->primary(['id', 'sesion_id']);

            // Foreign keys
            $table->foreign('fecha_id')->references('id')->on('fechas');
            $table->foreign('sesion_id')->references('id')->on('sesiones_definicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
