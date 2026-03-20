@extends('layouts.admin')

@section('title', 'Vista Previa Pilotos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Revisar y Confirmar Pilotos'
])

<form action="{{ route('admin.pilotos.import.store') }}" method="POST">
    @csrf
    <input type="hidden" name="campeonato_id" value="{{ $campeonato_id }}">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0 fw-semibold">Pilotos Encontrados: {{ count($pilotos) }}</h5>
                    <p class="text-muted small mb-0 mt-1">Por favor verifica los nombres detectados antes de guardarlos. Puedes corregir cualquier error tipográfico.</p>
                </div>
                
                <div class="card-body-modern p-0">
                    <div class="table-responsive">
                        <table class="table-modern w-100">
                            <thead>
                                <tr>
                                    <th style="width: 10%">Orden</th>
                                    <th style="width: 15%">N° Auto</th>
                                    <th>Piloto</th>
                                    <th style="width: 20%">País</th>
                                    <th style="width: 15%">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pilotos as $index => $p)
                                <tr id="row-{{ $index }}">
                                    <td>
                                        <input type="number" 
                                               name="pilotos[{{ $index }}][orden]" 
                                               class="input-modern px-2 py-1" 
                                               value="{{ $p['orden'] ?? '' }}" 
                                               readonly 
                                               style="background: #f8f9fa;">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="pilotos[{{ $index }}][auto]" 
                                               class="input-modern px-2 py-1" 
                                               value="{{ $p['auto'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="pilotos[{{ $index }}][nombre]" 
                                               class="input-modern px-2 py-1" 
                                               value="{{ $p['nombre'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="pilotos[{{ $index }}][pais]" 
                                               class="input-modern px-2 py-1" 
                                               value="{{ $p['pais'] ?? 'Argentina' }}"
                                               placeholder="Ej. Argentina">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('row-{{ $index }}').remove()">
                                            <i class="fas fa-trash"></i> Descartar
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-body-modern border-top">
                    <div class="form-actions d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.pilotos.import.form') }}" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>
                        <button type="submit" class="btn-modern btn-primary-modern">
                            <i class="fas fa-save me-2"></i> Confirmar y Guardar Pilotos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
