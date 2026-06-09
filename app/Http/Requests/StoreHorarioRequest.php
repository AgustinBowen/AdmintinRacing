<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Horario;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_id' => 'required|uuid|exists:fechas,id',
            'sesion_id' => [
                'required',
                'uuid',
                'exists:sesiones_definicion,id',
                function ($attribute, $value, $fail) {
                    if (Horario::existePorSesion($value)) {
                        $fail('Ya existe un horario para esta sesión.');
                    }
                },
            ],
            'horario' => 'required|date_format:H:i',
            'duracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ];
    }
}
