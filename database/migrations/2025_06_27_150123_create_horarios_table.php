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
            $table->uuid('sesion_id')->unique();
            $table->uuid('fecha_id');
            $table->timestamp('horario');
            $table->string('duracion')->nullable(); 
            $table->text('observaciones')->nullable(); 
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

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
