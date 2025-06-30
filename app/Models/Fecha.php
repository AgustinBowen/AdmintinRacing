<?php

// app/Models/Fecha.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Fecha extends Model
{
    use HasUuids;
    
    protected $fillable = ['campeonato_id', 'nombre', 'fecha_desde', 'fecha_hasta', 'circuito_id'];
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
        return $this->belongsTo(Circuito::class, 'circuito_id');
    }
    public function sesiones()
    {
        return $this->hasMany(SesionDefinicion::class, 'fecha_id');
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