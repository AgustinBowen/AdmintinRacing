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
        Schema::create('circuitos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('distancia', 8, 3)->nullable(); // en km
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuitos');
    }
};
