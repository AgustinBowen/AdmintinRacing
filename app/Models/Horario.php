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

    // Clave primaria compuesta
    protected $primaryKey = ['id', 'sesion_id'];
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
     * Override del método getKey para manejar clave primaria compuesta
     */
    public function getKey()
    {
        $attributes = [];
        $keyNames = (array) $this->getKeyName();
        foreach ($keyNames as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }
        return $attributes;
    }

    /**
     * Override del método getKeyName para clave primaria compuesta
     */
    public function getKeyName()
    {
        return $this->primaryKey;
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
        
        // Asumiendo que la duración viene en formato PostgreSQL interval (ej: "01:30:00")
        return $this->duracion;
    }
}