@extends('layouts.admin')

@section('content')
<div class="view-head">
    <h1>{{ strtoupper($campeonato->nombre) }}</h1>
</div>

{{-- Actions --}}
<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.campeonatos.show', $campeonato) }}" class="btn ghost">
        <x-heroicon-o-arrow-left style="width:1em; height:1em; vertical-align:-0.125em;" /> Volver
    </a>
    <a href="{{ route('admin.campeonatos.scoring', $campeonato) }}" class="btn ghost">
        <x-heroicon-o-adjustments-horizontal style="width:1em; height:1em; vertical-align:-0.125em;" /> Sistema de Puntaje
    </a>
    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#confirmSyncModal">
        <x-heroicon-o-arrow-path style="width:1em; height:1em; vertical-align:-0.125em;" /> Sincronizar Puntos
    </button>
</div>

{{-- Modal Confirmar Sincronización --}}
<div class="custom-modal" id="confirmSyncModal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <x-heroicon-o-arrow-path style="width:1em; height:1em; vertical-align:-0.125em; color: var(--white); margin-right: 8px;" /> Confirmar Sincronización
            </h5>
            <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p style="margin-bottom: 12px; font-size: 15px;">¿Estás seguro de que querés <strong>sincronizar todos los puntos</strong> del campeonato <strong>{{ $campeonato->nombre }}</strong>?</p>
            
            <div style="background-color: var(--carbon-2); border: 1px dashed var(--gray); padding: 12px; margin-top: 16px;">
                <ul style="margin: 0; padding-left: 20px; color: var(--bone); font-size: 13px; line-height: 1.6;">
                    <li>Se recalcularán los puntos de cada piloto en cada fecha.</li>
                    <li>Se guardarán los puntos individuales en cada sesión.</li>
                    <li>Se actualizará la tabla de posiciones general.</li>
                </ul>
            </div>

            <div style="margin-top: 16px; color: var(--gray); font-size: 13px;">
                <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em;" /> Esta operación puede tardar unos segundos.
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn ghost" data-dismiss="modal">
                Cancelar
            </button>
            <form method="POST" action="{{ route('admin.campeonatos.sync', $campeonato) }}" id="syncForm">
                @csrf
                <button type="submit" class="btn" id="btnSyncSubmit">
                    Sincronizar Ahora
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncForm = document.getElementById('syncForm');
    const btnSyncSubmit = document.getElementById('btnSyncSubmit');
    const modal = document.getElementById('confirmSyncModal');
    
    if (syncForm && btnSyncSubmit) {
        syncForm.addEventListener('submit', function() {
            btnSyncSubmit.disabled = true;
            btnSyncSubmit.innerHTML = 'Sincronizando...';
        });
    }

    // Modal Logic
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-bs-toggle="modal"][data-bs-target="#confirmSyncModal"]');
        if (toggleBtn) {
            e.preventDefault();
            modal.classList.add('show');
        }

        const dismissBtn = e.target.closest('#confirmSyncModal [data-dismiss="modal"]');
        if (dismissBtn) {
            modal.classList.remove('show');
        }
    });
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    }
});
</script>

@if(empty($standings))
<div class="form-card text-center" style="padding: 40px; color: var(--gray);">
    <x-heroicon-o-flag style="width:1em; height:1em; vertical-align:-0.125em; font-size: 32px; margin-bottom: 16px;" />
    <p style="margin: 0;">Todavía no hay resultados cargados para calcular la clasificación.</p>
</div>
@else
<div class="tbl-wrap" style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--black); border-bottom: 1px solid var(--carbon);">
        <h5 style="margin: 0; font-family: var(--font-oswald); font-size: 16px; text-transform: uppercase;">
            <x-heroicon-o-trophy style="width:1em; height:1em; vertical-align:-0.125em; margin-right: 8px;" /> Tabla de Posiciones
        </h5>
        <span style="font-size: 12px; color: var(--gray); font-family: var(--font-sans);">
            {{ count($standings) }} PILOTOS
        </span>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">Pos.</th>
                <th style="width: 50px; text-align: center;">N°</th>
                <th>Piloto</th>
                @foreach($fechas as $fecha)
                <th style="text-align: center;" title="{{ $fecha->nombre }}">
                    F{{ $loop->iteration }}
                </th>
                @endforeach
                <th style="text-align: center; color: var(--racing);">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($standings as $row)
            @php $piloto = $row['piloto']; @endphp
            <tr>
                <td style="text-align: center; font-weight: 700; font-family: var(--font-oswald); font-size: 16px;">
                    @if($row['posicion'] === 1)
                        <span style="color: #f59e0b;">1°</span>
                    @elseif($row['posicion'] === 2)
                        <span style="color: var(--gray);">2°</span>
                    @elseif($row['posicion'] === 3)
                        <span style="color: #b45309;">3°</span>
                    @else
                        {{ $row['posicion'] }}°
                    @endif
                </td>
                <td style="text-align: center; color: var(--gray);">
                    @php
                        $numeroPivot = $piloto?->campeonatos
                            ->firstWhere('id', $campeonato->id)
                            ?->pivot->numero_auto ?? '—';
                    @endphp
                    {{ $numeroPivot }}
                </td>
                <td style="font-weight: 500; font-size: 15px; color: var(--bone);">
                    {{ $piloto?->nombre ?? 'Piloto desconocido' }}
                </td>
                @foreach($fechas as $fecha)
                @php $fd = $row['fechas'][$fecha->id] ?? null; @endphp
                <td style="text-align: center;">
                    @if($fd)
                        @if($fd['excluido_evento'])
                            <span class="badge" style="background: var(--racing); color: var(--white); font-size: 10px;">EXC</span>
                        @else
                            <a href="{{ route('admin.resultados.index', ['fecha_id' => $fecha->id, 'piloto_id' => $piloto?->id]) }}" style="text-decoration: none;">
                                <span style="display: block; font-weight: 600; color: var(--white);">{{ $fd['total'] }}</span>
                                <span style="display: block; color: var(--gray); font-size: 10px; margin-top: 2px;">
                                    @if($fd['presentacion']) P:{{ $fd['presentacion'] }} @endif
                                    @if($fd['clasificacion']) C:{{ $fd['clasificacion'] }} @endif
                                    @if($fd['series']) S:{{ $fd['series'] }} @endif
                                    @if($fd['final']) F:{{ $fd['final'] }} @endif
                                </span>
                            </a>
                        @endif
                    @else
                        <span style="color: var(--carbon);">-</span>
                    @endif
                </td>
                @endforeach
                <td style="text-align: center; font-weight: 700; color: var(--white); font-size: 16px;">
                    {{ $row['total'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Legend --}}
<div style="margin-top: 16px; color: var(--gray); font-size: 12px; font-family: var(--font-sans);">
    <strong>Leyenda:</strong> P = Presentación &nbsp;·&nbsp; C = Clasificación &nbsp;·&nbsp; S = Series &nbsp;·&nbsp; F = Final
</div>
@endif

@endsection
