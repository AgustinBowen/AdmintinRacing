<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickStorePilotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:pilotos,nombre',
            'pais'   => 'nullable|string|max:100',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
            'numero_auto'   => 'nullable|integer'
        ];
    }
}
