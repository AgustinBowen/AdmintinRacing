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
        if ($this->tiempo_total === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('tiempo_total');

        // Si es null o vacío, retornar guion
        if ($valor === null || $valor === '') return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Rellenar con ceros a la izquierda para asegurar longitud mínima
        $valor = str_pad($valor, 6, '0', STR_PAD_LEFT);

        // Obtener la longitud del valor
        $longitud = strlen($valor);

        // Milisegundos: últimos 3 dígitos
        $milisegundos = intval(substr($valor, -3));

        // Segundos: 2 dígitos antes de los milisegundos
        $segundos = intval(substr($valor, -5, 2));

        // Minutos: todo lo que queda al principio
        $minutos = intval(substr($valor, 0, $longitud - 5));

        return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear mejor tiempo
     */
    public function getMejorTiempoFormateadoAttribute()
    {
        if ($this->mejor_tiempo === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('mejor_tiempo');

        // Si es null o vacío, retornar guion
        if ($valor === null || $valor === '') return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Rellenar con ceros a la izquierda para asegurar longitud mínima
        $valor = str_pad($valor, 6, '0', STR_PAD_LEFT);

        // Obtener la longitud del valor
        $longitud = strlen($valor);

        // Milisegundos: últimos 3 dígitos
        $milisegundos = intval(substr($valor, -3));

        // Segundos: 2 dígitos antes de los milisegundos
        $segundos = intval(substr($valor, -5, 2));

        // Minutos: todo lo que queda al principio
        $minutos = intval(substr($valor, 0, $longitud - 5));

        return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear diferencia con el primero
     */
    public function getDiferenciaPrimeroFormateadaAttribute()
    {
        if ($this->diferencia_primero === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('diferencia_primero');

        // Si es null, vacío o cero, retornar guion o +0.000
        if ($valor === null || $valor === '' || $valor == 0) return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Determinar si es negativo (aunque normalmente diferencias son positivas)
        $esNegativo = $valor < 0;
        $valorAbsoluto = abs($valor);

        // Convertir a string el valor absoluto
        $valorStr = (string)$valorAbsoluto;

        // Determinar la longitud y formatear según el tamaño
        $longitud = strlen($valorStr);

        if ($longitud <= 3) {
            // Casos como 423 = 0.423 segundos
            $valorStr = str_pad($valorStr, 3, '0', STR_PAD_LEFT);
            $segundos = 0;
            $milisegundos = intval($valorStr);
        } else if ($longitud == 4) {
            // Casos como 5423 = 5.423 segundos
            $segundos = intval(substr($valorStr, 0, 1));
            $milisegundos = intval(substr($valorStr, 1, 3));
        } else if ($longitud == 5) {
            // Casos como 15423 = 15.423 segundos
            $segundos = intval(substr($valorStr, 0, 2));
            $milisegundos = intval(substr($valorStr, 2, 3));
        } else if ($longitud >= 6) {
            // Casos como 125423 = 1:25.423 (1 minuto, 25 segundos, 423 milisegundos)
            $valorStr = str_pad($valorStr, 6, '0', STR_PAD_LEFT);
            $minutos = intval(substr($valorStr, 0, $longitud - 5));
            $segundos = intval(substr($valorStr, -5, 2));
            $milisegundos = intval(substr($valorStr, -3));

            $signo = $esNegativo ? '-' : '+';
            return sprintf('%s%d:%02d.%03d', $signo, $minutos, $segundos, $milisegundos);
        }

        // Para casos de solo segundos (menos de 1 minuto)
        $signo = $esNegativo ? '-' : '+';
        return sprintf('%s%d.%03d', $signo, $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear sector 1
     */
    public function getSector1FormateadoAttribute()
    {
        if ($this->sector_1 === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('sector_1');

        // Si es null, vacío o cero, retornar guion
        if ($valor === null || $valor === '' || $valor == 0) return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Determinar si es negativo
        $esNegativo = $valor < 0;
        $valorAbsoluto = abs($valor);

        // Convertir a string el valor absoluto
        $valorStr = (string)$valorAbsoluto;

        // Determinar la longitud y formatear según el tamaño
        $longitud = strlen($valorStr);

        if ($longitud <= 3) {
            // Casos como 423 = 0.423 segundos
            $valorStr = str_pad($valorStr, 3, '0', STR_PAD_LEFT);
            $segundos = 0;
            $milisegundos = intval($valorStr);
        } else if ($longitud == 4) {
            // Casos como 5423 = 5.423 segundos
            $segundos = intval(substr($valorStr, 0, 1));
            $milisegundos = intval(substr($valorStr, 1, 3));
        } else if ($longitud == 5) {
            // Casos como 15423 = 15.423 segundos
            $segundos = intval(substr($valorStr, 0, 2));
            $milisegundos = intval(substr($valorStr, 2, 3));
        } else if ($longitud >= 6) {
            // Casos como 125423 = 1:25.423 (1 minuto, 25 segundos, 423 milisegundos)
            $valorStr = str_pad($valorStr, 6, '0', STR_PAD_LEFT);
            $minutos = intval(substr($valorStr, 0, $longitud - 5));
            $segundos = intval(substr($valorStr, -5, 2));
            $milisegundos = intval(substr($valorStr, -3));

            return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
        }

        // Para casos de solo segundos (menos de 1 minuto)
        return sprintf('%d.%03d', $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear sector 2
     */
    public function getSector2FormateadoAttribute()
    {
        if ($this->sector_2 === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('sector_2');

        // Si es null, vacío o cero, retornar guion
        if ($valor === null || $valor === '' || $valor == 0) return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Determinar si es negativo
        $esNegativo = $valor < 0;
        $valorAbsoluto = abs($valor);

        // Convertir a string el valor absoluto
        $valorStr = (string)$valorAbsoluto;

        // Determinar la longitud y formatear según el tamaño
        $longitud = strlen($valorStr);

        if ($longitud <= 3) {
            // Casos como 423 = 0.423 segundos
            $valorStr = str_pad($valorStr, 3, '0', STR_PAD_LEFT);
            $segundos = 0;
            $milisegundos = intval($valorStr);
        } else if ($longitud == 4) {
            // Casos como 5423 = 5.423 segundos
            $segundos = intval(substr($valorStr, 0, 1));
            $milisegundos = intval(substr($valorStr, 1, 3));
        } else if ($longitud == 5) {
            // Casos como 15423 = 15.423 segundos
            $segundos = intval(substr($valorStr, 0, 2));
            $milisegundos = intval(substr($valorStr, 2, 3));
        } else if ($longitud >= 6) {
            // Casos como 125423 = 1:25.423 (1 minuto, 25 segundos, 423 milisegundos)
            $valorStr = str_pad($valorStr, 6, '0', STR_PAD_LEFT);
            $minutos = intval(substr($valorStr, 0, $longitud - 5));
            $segundos = intval(substr($valorStr, -5, 2));
            $milisegundos = intval(substr($valorStr, -3));

            return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
        }

        // Para casos de solo segundos (menos de 1 minuto)
        return sprintf('%d.%03d', $segundos, $milisegundos);
    }

    /**
     * Accessor para formatear sector 3
     */
    public function getSector3FormateadoAttribute()
    {
        if ($this->sector_3 === null) return '—';

        // Obtener el valor raw del atributo (sin casting)
        $valor = $this->getOriginal('sector_3');

        // Si es null, vacío o cero, retornar guion
        if ($valor === null || $valor === '' || $valor == 0) return '—';

        // Convertir a string y quitar decimales si los hay
        $valor = explode('.', (string)$valor)[0];

        // Determinar si es negativo
        $esNegativo = $valor < 0;
        $valorAbsoluto = abs($valor);

        // Convertir a string el valor absoluto
        $valorStr = (string)$valorAbsoluto;

        // Determinar la longitud y formatear según el tamaño
        $longitud = strlen($valorStr);

        if ($longitud <= 3) {
            // Casos como 423 = 0.423 segundos
            $valorStr = str_pad($valorStr, 3, '0', STR_PAD_LEFT);
            $segundos = 0;
            $milisegundos = intval($valorStr);
        } else if ($longitud == 4) {
            // Casos como 5423 = 5.423 segundos
            $segundos = intval(substr($valorStr, 0, 1));
            $milisegundos = intval(substr($valorStr, 1, 3));
        } else if ($longitud == 5) {
            // Casos como 15423 = 15.423 segundos
            $segundos = intval(substr($valorStr, 0, 2));
            $milisegundos = intval(substr($valorStr, 2, 3));
        } else if ($longitud >= 6) {
            // Casos como 125423 = 1:25.423 (1 minuto, 25 segundos, 423 milisegundos)
            $valorStr = str_pad($valorStr, 6, '0', STR_PAD_LEFT);
            $minutos = intval(substr($valorStr, 0, $longitud - 5));
            $segundos = intval(substr($valorStr, -5, 2));
            $milisegundos = intval(substr($valorStr, -3));

            return sprintf('%d:%02d.%03d', $minutos, $segundos, $milisegundos);
        }

        // Para casos de solo segundos (menos de 1 minuto)
        return sprintf('%d.%03d', $segundos, $milisegundos);
    }
}
