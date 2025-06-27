<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PilotoCampeonato extends Model
{
    use HasUuids;
    
    protected $table = 'pilotos_campeonato';
    protected $fillable = ['piloto_id', 'campeonato_id', 'numero_auto'];
    public $timestamps = false;
    
    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }
    
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }
}
