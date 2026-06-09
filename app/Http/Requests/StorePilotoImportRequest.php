<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePilotoImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pilotos' => 'required|array',
            'pilotos.*.nombre' => 'required|string|max:255',
            'pilotos.*.pais' => 'required|string|max:100',
            'pilotos.*.auto' => 'required|numeric',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
        ];
    }
}
