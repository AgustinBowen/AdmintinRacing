<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo para PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Normalmente no eliminamos extensiones en el rollback por seguridad,
        // pero si fuera necesario:
        // if (config('database.default') === 'pgsql') {
        //     DB::statement('DROP EXTENSION IF EXISTS unaccent;');
        // }
    }
};
