<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResultadoSesionRequest extends FormRequest
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
            'piloto_id' => 'required|exists:pilotos,id',
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
}
