<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistema_puntaje_fecha', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fecha_id');
            $table->string('tipo_sesion'); // presentacion | clasificacion | serie | final
            $table->unsignedTinyInteger('posicion')->nullable(); // null = applies to all (presentacion)
            $table->unsignedSmallInteger('puntos')->default(0);

            $table->foreign('fecha_id')
                  ->references('id')
                  ->on('fechas')
                  ->onDelete('cascade');

            $table->unique(['fecha_id', 'tipo_sesion', 'posicion']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistema_puntaje_fecha');
    }
};
