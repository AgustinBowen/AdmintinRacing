<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultadoSesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'tiempo_total' => \App\Helpers\TimeHelper::timeToSeconds($this->input('tiempo_total')),
            'mejor_tiempo' => \App\Helpers\TimeHelper::timeToSeconds($this->input('mejor_tiempo')),
            'diferencia_primero' => \App\Helpers\TimeHelper::timeToSeconds($this->input('diferencia_primero')),
            'sector_1' => \App\Helpers\TimeHelper::timeToSeconds($this->input('sector_1')),
            'sector_2' => \App\Helpers\TimeHelper::timeToSeconds($this->input('sector_2')),
            'sector_3' => \App\Helpers\TimeHelper::timeToSeconds($this->input('sector_3')),
        ]);
    }

    public function rules(): array
    {
        return [
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'piloto_id' => [
                'required',
                'exists:pilotos,id',
                Rule::unique('resultados_sesion')->where(function ($query) {
                    return $query->where('sesion_id', $this->sesion_id);
                }),
            ],
            'posicion' => 'required|integer|min:1',
            'puntos' => 'nullable|integer|min:0',
            'vueltas' => 'nullable|integer|min:0',
            'tiempo_total' => 'nullable|numeric|min:0',
            'mejor_tiempo' => 'nullable|numeric|min:0',
            'diferencia_primero' => 'nullable|numeric|min:0',
            'sector_1' => 'nullable|numeric|min:0',
            'sector_2' => 'nullable|numeric|min:0',
            'sector_3' => 'nullable|numeric|min:0',
            'excluido' => 'boolean',
            'presente' => 'boolean',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'sesion_id.required' => 'Debe seleccionar una sesión.',
            'sesion_id.exists' => 'La sesión seleccionada no es válida.',

            'piloto_id.required' => 'Debe seleccionar un piloto.',
            'piloto_id.exists' => 'El piloto seleccionado no es válido.',
            'piloto_id.unique' => 'Ya existe un resultado para este piloto en esta sesión.',

            'posicion.required' => 'Debe ingresar la posición del piloto.',
            'posicion.integer' => 'La posición debe ser un número entero.',
            'posicion.min' => 'La posición mínima permitida es 1.',

            'puntos.integer' => 'Los puntos deben ser un número entero.',
            'puntos.min' => 'Los puntos no pueden ser negativos.',

            'vueltas.integer' => 'La cantidad de vueltas debe ser un número entero.',
            'vueltas.min' => 'Las vueltas no pueden ser negativas.',

            'tiempo_total.numeric' => 'El tiempo total debe ser un número.',
            'tiempo_total.min' => 'El tiempo total no puede ser negativo.',

            'mejor_tiempo.numeric' => 'El mejor tiempo debe ser un número.',
            'mejor_tiempo.min' => 'El mejor tiempo no puede ser negativo.',

            'diferencia_primero.numeric' => 'La diferencia con el primero debe ser un número.',
            'diferencia_primero.min' => 'La diferencia no puede ser negativa.',

            'sector_1.numeric' => 'El tiempo del sector 1 debe ser un número.',
            'sector_1.min' => 'El tiempo del sector 1 no puede ser negativo.',

            'sector_2.numeric' => 'El tiempo del sector 2 debe ser un número.',
            'sector_2.min' => 'El tiempo del sector 2 no puede ser negativo.',

            'sector_3.numeric' => 'El tiempo del sector 3 debe ser un número.',
            'sector_3.min' => 'El tiempo del sector 3 no puede ser negativo.',

            'excluido.boolean' => 'El campo "excluido" debe ser verdadero o falso.',
            'presente.boolean' => 'El campo "presente" debe ser verdadero o falso.',

            'observaciones.string' => 'Las observaciones deben ser un texto válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 1000 caracteres.',
        ];
    }
}
