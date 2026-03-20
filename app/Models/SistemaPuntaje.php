<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SistemaPuntaje extends Model
{
    use HasUuids;

    protected $table = 'sistema_puntaje';

    protected $fillable = [
        'campeonato_id',
        'tipo_sesion',
        'posicion',
        'puntos',
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntos'   => 'integer',
    ];

    // Human-readable labels for each session type
    const TIPO_LABELS = [
        'presentacion' => 'Presentación',
        'clasificacion' => 'Clasificación (Pole)',
        'serie'        => 'Serie Clasificatoria',
        'final'        => 'Carrera Final',
    ];

    // Default scoring table per ART. 4.3.3 (standard dates): 1pt presentation
    const DEFAULT_SCORING = [
        ['tipo_sesion' => 'presentacion', 'posicion' => null, 'puntos' => 1],
        ['tipo_sesion' => 'clasificacion', 'posicion' => 1,   'puntos' => 1],
        // Series: positions 1-6
        ['tipo_sesion' => 'serie', 'posicion' => 1, 'puntos' => 6],
        ['tipo_sesion' => 'serie', 'posicion' => 2, 'puntos' => 5],
        ['tipo_sesion' => 'serie', 'posicion' => 3, 'puntos' => 4],
        ['tipo_sesion' => 'serie', 'posicion' => 4, 'puntos' => 3],
        ['tipo_sesion' => 'serie', 'posicion' => 5, 'puntos' => 2],
        ['tipo_sesion' => 'serie', 'posicion' => 6, 'puntos' => 1],
        // Finals: positions 1-20
        ['tipo_sesion' => 'final', 'posicion' => 1,  'puntos' => 30],
        ['tipo_sesion' => 'final', 'posicion' => 2,  'puntos' => 26],
        ['tipo_sesion' => 'final', 'posicion' => 3,  'puntos' => 23],
        ['tipo_sesion' => 'final', 'posicion' => 4,  'puntos' => 21],
        ['tipo_sesion' => 'final', 'posicion' => 5,  'puntos' => 19],
        ['tipo_sesion' => 'final', 'posicion' => 6,  'puntos' => 17],
        ['tipo_sesion' => 'final', 'posicion' => 7,  'puntos' => 15],
        ['tipo_sesion' => 'final', 'posicion' => 8,  'puntos' => 13],
        ['tipo_sesion' => 'final', 'posicion' => 9,  'puntos' => 12],
        ['tipo_sesion' => 'final', 'posicion' => 10, 'puntos' => 11],
        ['tipo_sesion' => 'final', 'posicion' => 11, 'puntos' => 10],
        ['tipo_sesion' => 'final', 'posicion' => 12, 'puntos' => 9],
        ['tipo_sesion' => 'final', 'posicion' => 13, 'puntos' => 8],
        ['tipo_sesion' => 'final', 'posicion' => 14, 'puntos' => 7],
        ['tipo_sesion' => 'final', 'posicion' => 15, 'puntos' => 6],
        ['tipo_sesion' => 'final', 'posicion' => 16, 'puntos' => 5],
        ['tipo_sesion' => 'final', 'posicion' => 17, 'puntos' => 4],
        ['tipo_sesion' => 'final', 'posicion' => 18, 'puntos' => 3],
        ['tipo_sesion' => 'final', 'posicion' => 19, 'puntos' => 2],
        ['tipo_sesion' => 'final', 'posicion' => 20, 'puntos' => 1],
    ];

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }
}
