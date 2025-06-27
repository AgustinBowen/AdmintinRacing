<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoSesion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'resultados_sesion';

    protected $fillable = [
        'sesion_id',
        'piloto_id',
        'posicion',
        'puntos',
        'vueltas',
        'tiempo_total',
        'mejor_tiempo',
        'diferencia_primero',
        'sector_1',
        'sector_2',
        'sector_3',
        'excluido',
        'presente',
        'observaciones'
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntos' => 'integer',
        'vueltas' => 'integer',
        'tiempo_total' => 'decimal:6',
        'mejor_tiempo' => 'decimal:6',
        'diferencia_primero' => 'float',
        'sector_1' => 'float',
        'sector_2' => 'float',
        'sector_3' => 'float',
        'excluido' => 'boolean',
        'presente' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con SesionDefinicion
     */
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionDefinicion::class, 'sesion_id');
    }

    /**
     * Relación con Piloto
     */
    public function piloto(): BelongsTo
    {
        return $this->belongsTo(Piloto::class, 'piloto_id');
    }

    /**
     * Scope para filtrar por pilotos presentes
     */
    public function scopePresente($query)
    {
        return $query->where('presente', true);
    }

    /**
     * Scope para filtrar por pilotos no excluidos
     */
    public function scopeNoExcluido($query)
    {
        return $query->where('excluido', false);
    }

    /**
     * Scope para ordenar por posición
     */
    public function scopeOrdenadoPorPosicion($query)
    {
        return $query->orderBy('posicion');
    }

    /**
     * Scope para filtrar por sesión
     */
    public function scopePorSesion($query, $sesionId)
    {
        return $query->where('sesion_id', $sesionId);
    }

    /**
     * Accessor para formatear tiempo total
     */
    public function getTiempoTotalFormateadoAttribute()
    {
        if (!$this->tiempo_total) return null;
        
        $segundos = floor($this->tiempo_total);
        $milisegundos = ($this->tiempo_total - $segundos) * 1000;
        $minutos = floor($segundos / 60);
        $segundos = $segundos % 60;
        
        return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear mejor tiempo
     */
    public function getMejorTiempoFormateadoAttribute()
    {
        if (!$this->mejor_tiempo) return null;
        
        $segundos = floor($this->mejor_tiempo);
        $milisegundos = ($this->mejor_tiempo - $segundos) * 1000;
        $minutos = floor($segundos / 60);
        $segundos = $segundos % 60;
        
        return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
    }
}