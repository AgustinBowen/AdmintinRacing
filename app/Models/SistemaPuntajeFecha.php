<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SistemaPuntajeFecha extends Model
{
    use HasUuids;

    protected $table = 'sistema_puntaje_fecha';

    protected $fillable = [
        'fecha_id',
        'tipo_sesion',
        'posicion',
        'puntos',
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntos'   => 'integer',
    ];

    public function fecha(): BelongsTo
    {
        return $this->belongsTo(Fecha::class);
    }
}
