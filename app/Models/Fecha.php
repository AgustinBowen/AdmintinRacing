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

    public function sistemaPuntaje()
    {
        return $this->hasMany(SistemaPuntajeFecha::class, 'fecha_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($fecha) {
            // Eliminar sesiones una por una para que disparen sus propios eventos (como borrar resultados y horarios de cada sesion)
            $fecha->sesiones->each->delete();
            
            // Eliminar los horarios directamente atados a la fecha
            $fecha->horarios()->delete();
            
            // Eliminar imagenes
            $fecha->imagenes()->delete();
            
            // Eliminar sistema de puntaje particular de esta fecha
            $fecha->sistemaPuntaje()->delete();
        });
    }
}