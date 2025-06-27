<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Imagen extends Model
{
    use HasUuids;
    
    protected $table = 'imagenes';
    protected $fillable = ['fecha_id', 'titulo', 'descripcion', 'url_cloudinary'];
    
    public function fecha()
    {
        return $this->belongsTo(Fecha::class);
    }
}
