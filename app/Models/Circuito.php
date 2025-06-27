<?php
// app/Models/Circuito.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circuito extends Model
{
    protected $fillable = ['nombre', 'distancia'];
    
    public function fechas()
    {
        return $this->hasMany(Fecha::class, 'circuito');
    }
}

