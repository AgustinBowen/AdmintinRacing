@extends('layouts.admin')
@section('title', 'Clasificación — ' . $campeonato->nombre)

@section('content')
@include('components.admin.page-header', [
    'title'    => 'Clasificación: ' . $campeonato->nombre,
    'subtitle' => 'Temporada ' . $campeonato->anio,
])

{{-- Actions --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.campeonatos.show', $campeonato) }}" class="btn-modern btn-secondary-modern">
        <i class="fas fa-arrow-left me-2"></i> Volver al Campeonato
    </a>
    <a href="{{ route('admin.campeonatos.scoring', $campeonato) }}" class="btn-modern btn-secondary-modern">
        <i class="fas fa-sliders me-2"></i> Sistema de Puntaje
    </a>
    <button type="button" class="btn-modern btn-primary-modern" data-bs-toggle="modal" data-bs-target="#confirmSyncModal">
        <i class="fas fa-sync-alt me-2"></i> Confirmar y Sincronizar Puntos
    </button>
</div>

{{-- Modal Confirmar Sincronización --}}
<div class="modal fade" id="confirmSyncModal" tabindex="-1" aria-labelledby="confirmSyncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmSyncModalLabel">
                    <i class="fas fa-sync-alt me-2 text-primary"></i> Confirmar Sincronización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Estás seguro de que querés <strong>sincronizar todos los puntos</strong> del campeonato <strong>{{ $campeonato->nombre }}</strong>?</p>
                
                <div class="p-3 rounded mb-3" style="background-color: var(--secondary-bg); border: 1px dashed var(--border-color);">
                    <ul class="mb-0 small text-muted">
                        <li>Se recalcularán los puntos de cada piloto en cada fecha.</li>
                        <li>Se guardarán los puntos individuales en cada sesión.</li>
                        <li>Se actualizará la tabla de posiciones general.</li>
                    </ul>
                </div>

                <div class="alert alert-info border-0 rounded-4 py-2 px-3 mb-0" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-2"></i> Esta operación puede tardar unos segundos.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <form method="POST" action="{{ route('admin.campeonatos.sync', $campeonato) }}" id="syncForm">
                    @csrf
                    <button type="submit" class="btn-modern btn-primary-modern" id="btnSyncSubmit">
                        <i class="fas fa-check me-1"></i> Sincronizar Ahora
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncForm = document.getElementById('syncForm');
    const btnSyncSubmit = document.getElementById('btnSyncSubmit');
    
    if (syncForm && btnSyncSubmit) {
        syncForm.addEventListener('submit', function() {
            btnSyncSubmit.disabled = true;
            btnSyncSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sincronizando...';
        });
    }
});
</script>

@if(empty($standings))
<div class="card-modern p-4 text-center text-muted">
    <i class="fas fa-flag-checkered fa-2x mb-3"></i>
    <p class="mb-0">Todavía no hay resultados cargados para calcular la clasificación.</p>
</div>
@else
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-trophy me-2"></i> Tabla de Posiciones
        </h5>
        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
            {{ count($standings) }} pilotos
        </span>
    </div>
    <div class="card-body-modern p-0">
        <div class="table-responsive">
            <table class="table-modern table-modern-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px" class="text-center">Pos.</th>
                        <th style="width:40px" class="text-center">N°</th>
                        <th>Piloto</th>
                        @foreach($fechas as $fecha)
                        <th class="text-center" title="{{ $fecha->nombre }}">
                            F{{ $loop->iteration }}
                        </th>
                        @endforeach
                        <th class="text-center fw-bold" style="min-width:70px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($standings as $row)
                    @php $piloto = $row['piloto']; @endphp
                    <tr>
                        <td class="text-center fw-bold">
                            @if($row['posicion'] === 1)
                                <span style="color: #f59e0b;">1°</span>
                            @elseif($row['posicion'] === 2)
                                <span style="color: hsl(var(--muted-foreground));">2°</span>
                            @elseif($row['posicion'] === 3)
                                <span style="color: #b45309;">3°</span>
                            @else
                                {{ $row['posicion'] }}°
                            @endif
                        </td>
                        <td class="text-center font-monospace text-muted">
                            @php
                                $numeroPivot = $piloto?->campeonatos
                                    ->firstWhere('id', $campeonato->id)
                                    ?->pivot->numero_auto ?? '—';
                            @endphp
                            {{ $numeroPivot }}
                        </td>
                        <td class="fw-medium">
                            {{ $piloto?->nombre ?? 'Piloto desconocido' }}
                        </td>
                        @foreach($fechas as $fecha)
                        @php $fd = $row['fechas'][$fecha->id] ?? null; @endphp
                        <td class="text-center">
                            @if($fd)
                                @if($fd['excluido_evento'])
                                    <span class="badge" style="background:hsl(var(--destructive));color:#fff;font-size:0.7rem;">EXC</span>
                                @else
                                    <a href="{{ route('admin.resultados.index', ['fecha_id' => $fecha->id, 'piloto_id' => $piloto->id]) }}" 
                                       class="text-decoration-none hover-lift" title="Ver/Editar resultados detallados">
                                        <span class="fw-semibold d-block text-dark">{{ $fd['total'] }}</span>
                                        <span class="d-block text-muted" style="font-size: 0.7rem; line-height: 1.1;">
                                            @if($fd['presentacion']) P:{{ $fd['presentacion'] }} @endif
                                            @if($fd['clasificacion']) C:{{ $fd['clasificacion'] }} @endif
                                            @if($fd['series']) S:{{ $fd['series'] }} @endif
                                            @if($fd['final']) F:{{ $fd['final'] }} @endif
                                        </span>
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="text-center fw-bold" style="font-size: 1.05rem;">
                            {{ $row['total'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="mt-3 text-muted" style="font-size: 0.78rem;">
    <strong>Leyenda:</strong> P = Presentación &nbsp;·&nbsp; C = Clasificación &nbsp;·&nbsp; S = Series &nbsp;·&nbsp; F = Final
</div>
@endif

@endsection
