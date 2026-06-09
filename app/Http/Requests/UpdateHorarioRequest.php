<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Horario;

class UpdateHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $horario = $this->route('horario');
        return [
            'fecha_id' => 'required|uuid|exists:fechas,id',
            'sesion_id' => [
                'required',
                'uuid',
                'exists:sesiones_definicion,id',
                function ($attribute, $value, $fail) use ($horario) {
                    // Check if another schedule (different from the current one) exists for this session
                    if (Horario::where('sesion_id', $value)->where('id', '!=', $horario->id)->exists()) {
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
