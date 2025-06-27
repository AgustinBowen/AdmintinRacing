<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PosicionCampeonato extends Model
{
    use HasUuids;
    
    protected $table = 'posiciones_campeonato';
    protected $fillable = ['campeonato_id', 'piloto_id', 'puntos_totales'];
    public $timestamps = false;
    
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }
    
    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }
}
