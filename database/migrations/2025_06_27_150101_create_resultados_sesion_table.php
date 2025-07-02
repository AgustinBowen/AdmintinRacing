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
        Schema::create('resultados_sesion', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sesion_id');
            $table->uuid('piloto_id');
            $table->integer('posicion')->nullable();
            $table->integer('puntos')->nullable();
            $table->bigInteger('vueltas')->nullable();
            $table->decimal('tiempo_total', 15, 6)->nullable();
            $table->decimal('mejor_tiempo', 15, 6)->nullable();
            $table->double('diferencia_primero')->nullable();
            $table->double('sector_1')->nullable();
            $table->double('sector_2')->nullable();
            $table->double('sector_3')->nullable();
            $table->boolean('excluido')->default(false);
            $table->boolean('presente')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Foreign keys
            $table->foreign('sesion_id')->references('id')->on('sesiones_definicion');
            $table->foreign('piloto_id')->references('id')->on('pilotos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados_sesion');
    }
};
