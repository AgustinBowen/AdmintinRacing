<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCampeonatoScoringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_sesion' => 'required|in:presentacion,clasificacion,serie,final',
            'posicion'    => 'nullable|integer|min:1|max:999',
            'puntos'      => 'required|integer|min:0|max:9999',
        ];
    }
}
