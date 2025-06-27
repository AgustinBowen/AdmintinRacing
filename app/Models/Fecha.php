<?php

// app/Models/Fecha.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Fecha extends Model
{
    use HasUuids;
    
    protected $fillable = ['campeonato_id', 'nombre', 'fecha_desde', 'fecha_hasta', 'circuito'];
    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];
    public $timestamps = false;
    
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }
    
    public function circuito()
    {
        return $this->belongsTo(Circuito::class, 'circuito');
    }
    
    public function carrerasFinales()
    {
        return $this->hasMany(CarreraFinal::class);
    }
    
    public function clasificaciones()
    {
        return $this->hasMany(Clasificacion::class);
    }
    
    public function entrenamientos()
    {
        return $this->hasMany(Entrenamiento::class);
    }
    
    public function seriesClasificatorias()
    {
        return $this->hasMany(SerieClasificatoria::class);
    }
    
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
    
    public function imagenes()
    {
        return $this->hasMany(Imagen::class);
    }
}