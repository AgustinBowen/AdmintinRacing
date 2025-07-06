<?php
// app/Models/Piloto.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Piloto extends Model
{
    use HasUuids;
    
    protected $fillable = ['nombre', 'pais'];
    public $timestamps = false;
    
    public function campeonatos()
    {
        return $this->belongsToMany(Campeonato::class, 'pilotos_campeonato')
                    ->withPivot('numero_auto');
    }
    
}