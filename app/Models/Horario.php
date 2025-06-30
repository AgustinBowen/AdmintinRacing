<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'horarios';

    // Clave primaria simple
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fecha_id',
        'sesion_id',
        'horario',
        'duracion',
        'observaciones'
    ];

    protected $casts = [
        'horario' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con Fecha
     */
    public function fecha(): BelongsTo
    {
        return $this->belongsTo(Fecha::class, 'fecha_id');
    }

    /**
     * Relación con SesionDefinicion
     */
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionDefinicion::class, 'sesion_id');
    }

    /**
     * Scope para verificar si existe un horario para una sesión
     */
    public function scopeExistePorSesion($query, $sesionId)
    {
        return $query->where('sesion_id', $sesionId)->exists();
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopePorFecha($query, $fechaId)
    {
        return $query->where('fecha_id', $fechaId);
    }

    /**
     * Scope para filtrar por sesión
     */
    public function scopePorSesion($query, $sesionId)
    {
        return $query->where('sesion_id', $sesionId);
    }

    /**
     * Scope para ordenar por horario
     */
    public function scopeOrdenadoPorHorario($query)
    {
        return $query->orderBy('horario');
    }

    /**
     * Accessor para formatear duración
     */
    public function getDuracionFormateadaAttribute()
    {
        if (!$this->duracion) return null;
        return $this->duracion;
    }
}