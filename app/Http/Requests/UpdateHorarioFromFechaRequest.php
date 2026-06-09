<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHorarioFromFechaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_sesion'  => 'required|date',
            'horario'       => 'required|date_format:H:i',
            'duracion'      => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ];
    }
}
