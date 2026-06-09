<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SesionDefinicion;

class StoreSesionDefinicionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_id' => 'required|exists:fechas,id',
            'fecha_sesion' => 'required|date',
            'tipo' => [
                'required',
                'in:' . implode(',', array_keys(SesionDefinicion::TIPOS)),
                Rule::unique('sesiones_definicion')->where(function ($query) {
                    return $query->where('fecha_id', $this->fecha_id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.unique' => 'Ya existe una sesión de este tipo para la fecha seleccionada.'
        ];
    }
}
