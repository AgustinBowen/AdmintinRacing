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
        Schema::create('fechas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campeonato_id');
            $table->string('nombre');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->unsignedBigInteger('circuito');

            $table->foreign('campeonato_id')->references('id')->on('campeonatos')->onDelete('cascade');
            $table->foreign('circuito')->references('id')->on('circuitos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fechas');
    }
};
