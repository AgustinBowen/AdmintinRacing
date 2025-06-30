<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionDefinicion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sesiones_definicion';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fecha_id',
        'tipo',
        'fecha_sesion', // Fecha de la sesión
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constantes para tipos de sesión
    const TIPOS = [
        'entrenamiento_1' => 'Entrenamiento 1',
        'entrenamiento_2' => 'Entrenamiento 2',
        'entrenamiento_3' => 'Entrenamiento 3',
        'clasificacion' => 'Clasificación',
        'serie_clasificatoria_1' => 'Serie Clasificatoria 1',
        'serie_clasificatoria_2' => 'Serie Clasificatoria 2',
        'serie_clasificatoria_3' => 'Serie Clasificatoria 3',
        'carrera_final' => 'Carrera Final'
    ];

    /**
     * Relación con Fecha
     */
    public function fecha(): BelongsTo
    {
        return $this->belongsTo(Fecha::class, 'fecha_id');
    }

    /**
     * Relación con ResultadosSesion
     */
    public function resultados(): HasMany
    {
        return $this->hasMany(ResultadoSesion::class, 'sesion_id');
    }

    /**
     * Relación con Horarios
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'sesion_id');
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Accessor para obtener el nombre del tipo
     */
    public function getTipoNombreAttribute()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
