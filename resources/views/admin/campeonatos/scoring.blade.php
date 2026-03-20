@extends('layouts.admin')
@section('title', 'Sistema de Puntaje — ' . $campeonato->nombre)

@section('content')
@include('components.admin.page-header', [
    'title'    => 'Sistema de Puntaje: ' . $campeonato->nombre,
    'subtitle' => 'Configurá los puntos para cada posición y tipo de sesión',
])

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.campeonatos.standings', $campeonato) }}" class="btn-modern btn-secondary-modern">
        <i class="fas fa-arrow-left me-2"></i> Ver Clasificación
    </a>
    <form method="POST" action="{{ route('admin.campeonatos.scoring.reset', $campeonato) }}"
          onsubmit="return confirm('¿Restablecer el sistema de puntaje a los valores predeterminados del reglamento?')">
        @csrf
        <button type="submit" class="btn-modern btn-destructive-modern">
            <i class="fas fa-rotate-left me-2"></i> Restablecer predeterminados
        </button>
    </form>
</div>

{{-- Reglas de puntaje --}}
<div class="card-modern mb-4 p-3" style="background: hsl(var(--accent)/0.07); border-left: 3px solid hsl(var(--accent));">
    <p class="mb-1 fw-semibold" style="font-size:0.9rem;"><i class="fas fa-info-circle me-2" style="color:hsl(var(--accent));"></i>Reglas de cálculo aplicadas</p>
    <ul class="mb-0 text-muted" style="font-size:0.82rem;">
        <li>La <strong>Presentación</strong> se aplica automáticamente a todo piloto con al menos un resultado no excluido en la fecha.</li>
        <li>Si un piloto es excluido de <strong>todos</strong> sus resultados en la fecha, sus puntos de la fecha quedan en <strong>0</strong>.</li>
        <li>Cada piloto corre en <strong>una sola serie</strong>; si apareciese en más, se toma la de mayor puntaje.</li>
    </ul>
</div>

@php
    $tipoLabels = \App\Models\SistemaPuntaje::TIPO_LABELS;
    $tipoOrder  = ['presentacion', 'clasificacion', 'serie', 'final'];
@endphp

@foreach($tipoOrder as $tipo)
<div class="card-modern mb-4">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            @php
                $icon = match($tipo) {
                    'presentacion'  => 'fa-id-card-clip',
                    'clasificacion' => 'fa-stopwatch',
                    'serie'         => 'fa-list-ol',
                    'final'         => 'fa-flag-checkered',
                    default         => 'fa-star',
                };
            @endphp
            <i class="fas {{ $icon }} me-2"></i> {{ $tipoLabels[$tipo] ?? $tipo }}
        </h5>
        {{-- Add new row button --}}
        @if($tipo !== 'presentacion')
        <button class="btn-modern btn-secondary-modern" style="font-size:0.8rem;padding:0.3rem 0.7rem;"
                data-bs-toggle="collapse" data-bs-target="#addRow-{{ $tipo }}">
            <i class="fas fa-plus me-1"></i> Agregar posición
        </button>
        @endif
    </div>
    <div class="card-body-modern p-0">
        {{-- Add row form (collapsed) --}}
        @if($tipo !== 'presentacion')
        <div class="collapse" id="addRow-{{ $tipo }}">
            <form method="POST" action="{{ route('admin.campeonatos.scoring.add', $campeonato) }}"
                  class="p-3 d-flex gap-2 align-items-end border-bottom">
                @csrf
                <input type="hidden" name="tipo_sesion" value="{{ $tipo }}">
                <div>
                    <label class="form-label fw-medium mb-1" style="font-size:0.8rem;">Posición</label>
                    <input type="number" name="posicion" class="input-modern" placeholder="Ej: 21" min="1" max="999" required style="width:100px;">
                </div>
                <div>
                    <label class="form-label fw-medium mb-1" style="font-size:0.8rem;">Puntos</label>
                    <input type="number" name="puntos" class="input-modern" placeholder="0" min="0" max="9999" required style="width:100px;">
                </div>
                <button type="submit" class="btn-modern btn-primary-modern" style="font-size:0.82rem;">
                    <i class="fas fa-plus me-1"></i> Agregar
                </button>
            </form>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table-modern mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40%">Condición</th>
                        <th style="width:30%">Puntos</th>
                        <th style="width:20%">Guardar</th>
                        <th style="width:10%" class="text-center">Borrar</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($scoring[$tipo]))
                    @foreach($scoring[$tipo] as $row)
                    <tr>
                        <td class="fw-medium">
                            @if($tipo === 'presentacion')
                                Presencia en la fecha
                            @elseif($tipo === 'clasificacion')
                                {{ $row->posicion }}° puesto (Pole)
                            @else
                                {{ $row->posicion }}° puesto
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('admin.campeonatos.scoring.update', [$campeonato, $row]) }}"
                                  class="d-flex align-items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="puntos" value="{{ $row->puntos }}"
                                       min="0" max="9999" class="input-modern" style="width:100px;">
                        </td>
                        <td>
                                <button type="submit" class="btn-modern btn-primary-modern"
                                        style="padding:0.3rem 0.65rem; font-size:0.8rem;">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            @if($tipo !== 'presentacion')
                            <form method="POST"
                                  action="{{ route('admin.campeonatos.scoring.delete', [$campeonato, $row]) }}"
                                  onsubmit="return confirm('¿Eliminar la fila de puntaje para el puesto {{ $row->posicion }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-modern btn-destructive-modern"
                                        style="padding:0.3rem 0.65rem; font-size:0.8rem; width:36px;">
                                    <i class="fas fa-trash" style="font-size:0.75rem;"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr><td colspan="4" class="text-center text-muted py-3">Sin filas configuradas.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@endsection
