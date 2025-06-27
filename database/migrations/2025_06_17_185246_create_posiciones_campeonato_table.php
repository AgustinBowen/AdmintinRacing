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
        Schema::create('posiciones_campeonato', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campeonato_id');
            $table->uuid('piloto_id');
            $table->integer('puntos_totales')->default(0);

            $table->foreign('campeonato_id')->references('id')->on('campeonatos')->onDelete('cascade');
            $table->foreign('piloto_id')->references('id')->on('pilotos')->onDelete('cascade');

            $table->unique(['campeonato_id', 'piloto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posiciones_campeonato');
    }
};
