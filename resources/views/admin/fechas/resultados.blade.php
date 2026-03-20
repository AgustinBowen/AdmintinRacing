@extends('layouts.admin')

@section('title', 'Resultados Completos de ' . $fecha->nombre)

@section('content')
@include('components.admin.page-header', [
    'title' => 'Resultados Completos',
    'subtitle' => 'Fecha: ' . $fecha->nombre . ' - ' . ($fecha->circuito->nombre ?? 'Sin circuito')
])

<div class="mb-4">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn-modern btn-secondary-modern">
        <i class="fas fa-arrow-left me-2"></i> Volver a la Fecha
    </a>
    <a href="{{ route('admin.resultados.index', ['fecha_id' => $fecha->id]) }}" class="btn-modern btn-secondary-modern ms-2">
        <i class="fas fa-list me-2"></i> Administrar Resultados Indivíduales
    </a>
    <form method="POST" action="{{ route('admin.fechas.generar-acumulados', $fecha) }}" class="d-inline-block">
        @csrf
        <button type="submit" class="btn-modern btn-primary-modern">
            <i class="fas fa-calculator me-2"></i> Generar Acumulados
        </button>
    </form>
</div>

@empty($fecha->sesiones)
    <div class="alert alert-info border-0 rounded-4">
        <i class="fas fa-info-circle me-2"></i> No hay sesiones registradas para esta fecha.
    </div>
@else
    @foreach($fecha->sesiones as $sesion)
    <div class="card-modern mb-5">
        <div class="card-header-modern d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                {{ \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}
            </h5>
            <div class="d-flex align-items-center gap-2">
                @if(count($sesion->resultados) > 0)
                <button type="button" 
                    class="btn-modern btn-destructive-modern" 
                    style="font-size: 0.75rem; padding: 0.3rem 0.6rem;"
                    data-bs-toggle="modal" 
                    data-bs-target="#deleteModal"
                    data-delete-url="{{ route('admin.fechas.eliminar-resultados-sesion', $sesion) }}"
                    data-item-name="todos los resultados de {{ \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}">
                    <i class="fas fa-trash-alt me-1"></i> Borrar Sesión
                </button>
                @endif
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                    <i class="fas fa-flag-checkered me-1"></i> {{ count($sesion->resultados) }} Pilotos
                </span>
            </div>
        </div>
        
        <div class="card-body-modern p-0">
            @if(count($sesion->resultados) === 0)
                <div class="p-4 text-center text-muted">Aún no hay resultados cargados para esta sesión.</div>
            @else
                <div class="table-responsive">
                    <table class="table-modern table-modern-hover mb-0">
                        @if($sesion->tipo === 'acumulados')
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%" class="text-center">Pos</th>
                                <th style="width: 5%" class="text-center">N°</th>
                                <th style="width: 30%">Nombre</th>
                                <th style="width: 15%">Clase</th>
                                <th style="width: 15%" class="text-end">Mejor T° total</th>
                                <th style="width: 30%">En sesión</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sesion->resultados as $resultado)
                            <tr>
                                <td class="text-center fw-medium">{{ $resultado->posicion }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $resultado->piloto->numero_auto_pivot ?? '-' }}</span>
                                </td>
                                <td class="fw-medium">{{ $resultado->piloto->nombre }}</td>
                                <td class="text-muted">{{ $fecha->campeonato->nombre }}</td>
                                <td class="text-end font-monospace fw-bold">{{ $resultado->mejor_tiempo_formateado }}</td>
                                <td class="text-muted">{{ $resultado->observaciones }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @else
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%" class="text-center">Pos</th>
                                <th style="width: 5%" class="text-center">N°</th>
                                <th style="width: 25%">Nombre</th>
                                <th style="width: 5%" class="text-center">Vueltas</th>
                                <th style="width: 10%" class="text-end">Total T°</th>
                                <th style="width: 10%" class="text-end">Mejor Tm</th>
                                <th style="width: 10%" class="text-end">Dif. resp. 1°</th>
                                <th style="width: 10%" class="text-end">S1 Mejor</th>
                                <th style="width: 10%" class="text-end">S2 Mejor</th>
                                <th style="width: 10%" class="text-end">S3 Mejor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $clasificados = $sesion->resultados->filter(fn($r) => !$r->excluido && rtrim($r->posicion) !== '');
                                $noClasificados = $sesion->resultados->filter(fn($r) => $r->excluido || rtrim($r->posicion) === '');
                            @endphp

                            @foreach($clasificados as $resultado)
                            <tr>
                                <td class="text-center fw-medium">{{ $resultado->posicion }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $resultado->piloto->numero_auto_pivot ?? '-' }}</span>
                                </td>
                                <td class="fw-medium">{{ $resultado->piloto->nombre }}</td>
                                <td class="text-center">{{ $resultado->vueltas ?? '-' }}</td>
                                <td class="text-end font-monospace">{{ $resultado->tiempo_total_formateado }}</td>
                                <td class="text-end font-monospace">{{ $resultado->mejor_tiempo_formateado }}</td>
                                <td class="text-end font-monospace">{{ $resultado->diferencia_primero_formateada }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_1_formateado }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_2_formateado }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_3_formateado }}</td>
                            </tr>
                            @endforeach

                            @if($noClasificados->count() > 0)
                            <tr>
                                <td colspan="10" class="bg-light py-2 text-muted fw-semibold" style="font-size: 0.8rem; text-transform: uppercase;">
                                    No clasificado
                                </td>
                            </tr>
                            @foreach($noClasificados as $resultado)
                            <tr>
                                <td class="text-center fw-bold text-danger">
                                    {{ $resultado->excluido ? 'EX' : 'NT' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $resultado->piloto->numero_auto_pivot ?? '-' }}</span>
                                </td>
                                <td class="fw-medium text-danger">{{ $resultado->piloto->nombre }}</td>
                                <td class="text-center">{{ $resultado->vueltas ?? '-' }}</td>
                                <td class="text-end font-monospace">{{ $resultado->tiempo_total_formateado }}</td>
                                <td class="text-end font-monospace">{{ $resultado->mejor_tiempo_formateado }}</td>
                                <td class="text-end font-monospace">{{ $resultado->excluido ? 'EX' : 'NT' }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_1_formateado }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_2_formateado }}</td>
                                <td class="text-end font-monospace text-muted">{{ $resultado->sector_3_formateado }}</td>
                            </tr>
                            @endforeach
                            @endif

                        </tbody>
                        @endif
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif

@include('components.admin.delete-modal')

@endsection

@push('styles')
<style>
.table-modern td.font-monospace {
    font-size: 0.9rem;
}
.table-modern thead th {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: hsl(var(--muted-foreground));
    border-bottom: 2px solid hsl(var(--border) / 0.5);
}
</style>
@endpush
