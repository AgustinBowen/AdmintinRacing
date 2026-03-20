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
            // Laravel's change() method sometimes fails to drop enum check constraints in Postgres.
            // We explicitly drop it here to allow new session types like 'entrenamiento_4' and 'acumulados'.
            DB::statement('ALTER TABLE sesiones_definicion DROP CONSTRAINT IF EXISTS sesiones_definicion_tipo_check;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es estrictamente necesario restaurar la restricción ya que queremos que sea flexible, 
        // pero si se deseara volver al estado anterior (solo tipos 1, 2, 3), se podría añadir.
    }
};
