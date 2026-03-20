@extends('layouts.admin')
@section('title', 'Puntaje de Fecha — ' . $fecha->nombre)

@section('content')
@include('components.admin.page-header', [
    'title'    => 'Puntaje: ' . $fecha->nombre,
    'subtitle' => 'Puntaje personalizado para esta fecha · Campeonato: ' . $fecha->campeonato->nombre,
])

@if($scoring->isEmpty())
<div class="card-modern mb-4 p-4 text-center">
    <div class="mb-3">
        <i class="fas fa-info-circle fa-2x text-primary shadow-sm rounded-circle p-2" style="background: hsl(var(--primary)/0.1);"></i>
    </div>
    <h5 class="fw-bold mb-2">Usando puntaje del campeonato</h5>
    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
        Esta fecha sigue las reglas generales del campeonato. Podés verlas en el sistema de puntaje general o personalizarlas solo para esta fecha.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <a href="{{ route('admin.campeonatos.scoring', $fecha->campeonato_id) }}" class="btn-modern btn-secondary-modern">
            <i class="fas fa-eye me-2"></i> Ver reglas generales
        </a>
        <form method="POST" action="{{ route('admin.fechas.scoring.customize', $fecha) }}">
            @csrf
            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="fas fa-edit me-2"></i> Personalizar para esta fecha
            </button>
        </form>
    </div>
</div>
@else
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn-modern btn-secondary-modern">
        <i class="fas fa-arrow-left me-2"></i> Volver a la Fecha
    </a>
    <form method="POST" action="{{ route('admin.fechas.scoring.reset', $fecha) }}"
          onsubmit="return confirm('¿Eliminar el puntaje personalizado y volver al puntaje del campeonato?')">
        @csrf
        <button type="submit" class="btn-modern btn-destructive-modern">
            <i class="fas fa-rotate-left me-2"></i> Eliminar personalización
        </button>
    </form>
</div>

<div class="card-modern mb-4 p-3" style="background: hsl(var(--accent)/0.07); border-left: 3px solid hsl(var(--accent));">
    <p class="mb-1 fw-semibold" style="font-size:0.9rem;">
        <i class="fas fa-star me-2" style="color:hsl(var(--accent));"></i>Puntaje personalizado activo para esta fecha
    </p>
    <p class="mb-0 text-muted" style="font-size:0.82rem;">
        Este puntaje sobreescribe al del campeonato <strong>únicamente para esta fecha</strong>.
    </p>
</div>
@endif

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
        @if($tipo !== 'presentacion')
        <button class="btn-modern btn-secondary-modern" style="font-size:0.8rem;padding:0.3rem 0.7rem;"
                data-bs-toggle="collapse" data-bs-target="#addRow-{{ $tipo }}">
            <i class="fas fa-plus me-1"></i> Agregar posición
        </button>
        @endif
    </div>
    <div class="card-body-modern p-0">
        @if($tipo !== 'presentacion')
        <div class="collapse" id="addRow-{{ $tipo }}">
            <form method="POST" action="{{ route('admin.fechas.scoring.add', $fecha) }}"
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
                        <th style="width:38%">Condición</th>
                        <th style="width:32%">Puntos</th>
                        <th style="width:18%">Guardar</th>
                        <th style="width:12%" class="text-center">Borrar</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($scoring[$tipo]))
                    @foreach($scoring[$tipo] as $row)
                    <tr>
                        <td class="fw-medium">
                            @if($tipo === 'presentacion') Presencia en la fecha
                            @elseif($tipo === 'clasificacion') {{ $row->posicion }}° puesto (Pole)
                            @else {{ $row->posicion }}° puesto
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('admin.fechas.scoring.update', [$fecha, $row]) }}"
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
                                  action="{{ route('admin.fechas.scoring.delete', [$fecha, $row]) }}"
                                  onsubmit="return confirm('¿Eliminar el puesto {{ $row->posicion }}?')">
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
