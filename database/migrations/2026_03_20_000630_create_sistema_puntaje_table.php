<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistema_puntaje', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campeonato_id');
            // tipo_sesion: presentacion | clasificacion | serie | final
            $table->string('tipo_sesion');
            // posicion: null means "any" (e.g. presentacion), number means specific finishing position
            $table->unsignedTinyInteger('posicion')->nullable();
            $table->unsignedSmallInteger('puntos')->default(0);

            $table->foreign('campeonato_id')
                  ->references('id')
                  ->on('campeonatos')
                  ->onDelete('cascade');

            $table->unique(['campeonato_id', 'tipo_sesion', 'posicion']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistema_puntaje');
    }
};
