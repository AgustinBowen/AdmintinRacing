<?php
// app/Models/Campeonato.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Campeonato extends Model
{
    use HasUuids;
    
    protected $fillable = ['nombre', 'anio'];
    public $timestamps = false;
    
    public function fechas()
    {
        return $this->hasMany(Fecha::class);
    }
    
    public function pilotos()
    {
        return $this->belongsToMany(Piloto::class, 'pilotos_campeonato')
                    ->withPivot('numero_auto');
    }
    
    public function posicionesCampeonato()
    {
        return $this->hasMany(PosicionCampeonato::class);
    }
}