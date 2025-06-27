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
        Schema::create('pilotos_campeonato', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('piloto_id');
            $table->uuid('campeonato_id');
            $table->integer('numero_auto');

            $table->foreign('piloto_id')->references('id')->on('pilotos')->onDelete('cascade');
            $table->foreign('campeonato_id')->references('id')->on('campeonatos')->onDelete('cascade');

            $table->unique(['piloto_id', 'campeonato_id']);
            $table->unique(['campeonato_id', 'numero_auto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilotos_campeonato');
    }
};
