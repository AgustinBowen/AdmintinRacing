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
     * Helper param formatear segundos a tiempo (mm:ss.iii o ss.iii)
     */
    private function formatSecondsToTime($valor, $isDiferencia = false)
    {
        if ($valor === null || $valor === '') return '—';

        $esNegativo = (float)$valor < 0;
        $valor = abs((float) $valor);

        $minutos = floor($valor / 60);
        $segundos = floor($valor % 60);
        $milisegundos = round(($valor - floor($valor)) * 1000);

        $signo = '';
        if ($isDiferencia && $valor > 0) {
            $signo = $esNegativo ? '-' : '+';
        } elseif ($esNegativo) {
            $signo = '-';
        }

        if ($minutos > 0) {
            return sprintf('%s%d:%02d.%03d', $signo, (int)$minutos, (int)$segundos, (int)$milisegundos);
        }

        return sprintf('%s%d.%03d', $signo, (int)$segundos, (int)$milisegundos);
    }

    public function getTiempoTotalFormateadoAttribute()
    {
        return $this->formatSecondsToTime($this->getOriginal('tiempo_total'));
    }

    public function getMejorTiempoFormateadoAttribute()
    {
        return $this->formatSecondsToTime($this->getOriginal('mejor_tiempo'));
    }

    public function getDiferenciaPrimeroFormateadaAttribute()
    {
        $val = $this->getOriginal('diferencia_primero');
        if ($val === null || $val === '' || $val == 0) return '—';
        return $this->formatSecondsToTime($val, true);
    }

    public function getSector1FormateadoAttribute()
    {
        $val = $this->getOriginal('sector_1');
        if ($val === null || $val === '' || $val == 0) return '—';
        return $this->formatSecondsToTime($val);
    }

    public function getSector2FormateadoAttribute()
    {
        $val = $this->getOriginal('sector_2');
        if ($val === null || $val === '' || $val == 0) return '—';
        return $this->formatSecondsToTime($val);
    }

    public function getSector3FormateadoAttribute()
    {
        $val = $this->getOriginal('sector_3');
        if ($val === null || $val === '' || $val == 0) return '—';
        return $this->formatSecondsToTime($val);
    }
}
