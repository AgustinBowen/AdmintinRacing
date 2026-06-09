<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFechaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito_id' => 'required|exists:circuitos,id',
        ];
    }
}
