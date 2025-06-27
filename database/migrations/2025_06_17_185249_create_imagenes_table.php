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
        Schema::create('imagenes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fecha_id');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('url_cloudinary');
            $table->timestamps();

            $table->foreign('fecha_id')->references('id')->on('fechas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};
