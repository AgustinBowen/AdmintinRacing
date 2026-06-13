@extends('layouts.admin')

@section('content')
<div class="view-head">
    <h1>RESULTADOS: {{ strtoupper($fecha->nombre) }} {{ $fecha->circuito->nombre ?? 'SIN CIRCUITO' }}</h1>
</div>

<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn ghost">
        <x-heroicon-o-arrow-left style="width:1em; height:1em; vertical-align:-0.125em;" /> Volver a la Fecha
    </a>
    <a href="{{ route('admin.resultados.import.form') }}" class="btn ghost">
        <x-heroicon-o-photo style="width:1em; height:1em; vertical-align:-0.125em;" /> Importar Archivo
    </a>
    <a href="{{ route('admin.resultados.create') }}?fecha_id={{ $fecha->id }}" class="btn ghost">
        <x-heroicon-o-plus style="width:1em; height:1em; vertical-align:-0.125em;" /> Nuevo Resultado Manual
    </a>
    <a href="{{ route('admin.resultados.index', ['fecha_id' => $fecha->id]) }}" class="btn ghost">
        <x-heroicon-o-list-bullet style="width:1em; height:1em; vertical-align:-0.125em;" /> Administrar Individuales
    </a>
    <form method="POST" action="{{ route('admin.fechas.generar-acumulados', $fecha) }}" style="display: inline-block;">
        @csrf
        <button type="submit" class="btn" style="background: var(--white); color: var(--black);">
            <x-heroicon-o-calculator style="width:1em; height:1em; vertical-align:-0.125em;" /> Generar Acumulados
        </button>
    </form>
</div>

@empty($fecha->sesiones)
    <div class="form-card text-center" style="padding: 40px; color: var(--gray);">
        <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em; font-size: 32px; margin-bottom: 16px;" />
        <p style="margin: 0;">No hay sesiones registradas para esta fecha.</p>
    </div>
@else
    <style>
    .resultados-tabs {
        display: flex;
        margin-bottom: 24px;
        background: var(--carbon);
        border-radius: 4px 4px 0 0;
        overflow-x: auto;
        overflow-y: hidden;
        border-bottom: 1px solid var(--black);
        /* Esconder scrollbar en webkit pero permitir scroll */
        scrollbar-width: none;
    }
    .resultados-tabs::-webkit-scrollbar {
        display: none;
    }
    .resultados-tabs .tab-btn {
        flex: 0 0 auto;
        min-width: 60px;
        text-align: center;
        padding: 14px 16px;
        background: var(--carbon);
        color: var(--gray);
        border: none;
        font-family: var(--font-oswald);
        font-size: 14px;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s ease;
        border-right: 1px solid rgba(255,255,255,0.05);
        white-space: nowrap;
    }
    .resultados-tabs .tab-btn:last-child {
        border-right: none;
    }
    .resultados-tabs .tab-btn:hover {
        color: var(--white);
        background: rgba(255,255,255,0.05);
    }
    .resultados-tabs .tab-btn.active {
        background: var(--white);
        color: var(--black);
        position: relative;
        font-weight: 600;
    }
    .resultados-tabs .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid var(--white);
        z-index: 10;
    }
    </style>

    <div class="resultados-tabs">
        @foreach($fecha->sesiones as $index => $sesion)
            <button class="tab-btn {{ $index === 0 ? 'active' : '' }}" onclick="switchTab('sesion-{{ $sesion->id }}', this)" title="{{ \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}">
                {{ \App\Models\SesionDefinicion::ABREVIATURAS[$sesion->tipo] ?? \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}
            </button>
        @endforeach
    </div>

    @foreach($fecha->sesiones as $index => $sesion)
    <div class="tbl-wrap tab-pane" id="sesion-{{ $sesion->id }}" style="margin-bottom: 32px; {{ $index !== 0 ? 'display: none;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--black); border-bottom: 1px solid var(--carbon);">
            <h5 style="margin: 0; font-family: var(--font-oswald); font-size: 16px; text-transform: uppercase;">
                {{ \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}
            </h5>
            <div style="display: flex; align-items: center; gap: 12px;">
                @if(count($sesion->resultados) > 0)
                <button type="button" class="btn ghost" style="padding: 4px 12px; font-size: 12px; min-width: auto; color: var(--racing); border-color: transparent;"
                    data-bs-toggle="modal" 
                    data-bs-target="#deleteModal"
                    data-delete-url="{{ route('admin.fechas.eliminar-resultados-sesion', $sesion) }}"
                    data-item-name="todos los resultados de {{ \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo }}">
                    <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" /> Borrar Sesión
                </button>
                @endif
                <span style="font-size: 12px; color: var(--gray); font-family: var(--font-sans);">
                    <x-heroicon-o-flag style="width:1em; height:1em; vertical-align:-0.125em; margin-right: 4px;" /> {{ count($sesion->resultados) }} PILOTOS
                </span>
            </div>
        </div>
        
        @if(count($sesion->resultados) === 0)
            <div style="padding: 32px; text-align: center; color: var(--gray); font-family: var(--font-sans); font-size: 13px;">Aún no hay resultados cargados para esta sesión.</div>
        @else
            <table>
                @if($sesion->tipo === 'acumulados')
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">Pos</th>
                        <th style="width: 5%; text-align: center;">N°</th>
                        <th style="width: 30%">Nombre</th>
                        <th style="width: 15%">Clase</th>
                        <th style="width: 15%; text-align: right;">Mejor T° total</th>
                        <th style="width: 30%">En sesión</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sesion->resultados as $resultado)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--bone);">{{ $resultado->posicion }}</td>
                        <td style="text-align: center; color: var(--gray);">
                            {{ $resultado->piloto->numero_auto_pivot ?? '-' }}
                        </td>
                        <td style="font-weight: 500; color: var(--white);">{{ $resultado->piloto->nombre }}</td>
                        <td style="color: var(--gray);">{{ $fecha->campeonato->nombre }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->mejor_tiempo_formateado }}</td>
                        <td style="color: var(--gray); font-size: 12px;">{{ $resultado->observaciones }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @else
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">Pos</th>
                        <th style="width: 5%; text-align: center;">N°</th>
                        <th style="width: 25%">Nombre</th>
                        <th style="width: 5%; text-align: center;">Vueltas</th>
                        <th style="width: 10%; text-align: right;">Total T°</th>
                        <th style="width: 10%; text-align: right;">Mejor Tm</th>
                        <th style="width: 10%; text-align: right;">Dif. resp. 1°</th>
                        <th style="width: 10%; text-align: right;">S1 Mejor</th>
                        <th style="width: 10%; text-align: right;">S2 Mejor</th>
                        <th style="width: 10%; text-align: right;">S3 Mejor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $clasificados = $sesion->resultados->filter(fn($r) => !$r->excluido && rtrim($r->posicion) !== '');
                        $noClasificados = $sesion->resultados->filter(fn($r) => $r->excluido || rtrim($r->posicion) === '');
                    @endphp

                    @foreach($clasificados as $resultado)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--bone);">{{ $resultado->posicion }}</td>
                        <td style="text-align: center; color: var(--gray);">
                            {{ $resultado->piloto->numero_auto_pivot ?? '-' }}
                        </td>
                        <td style="font-weight: 500; color: var(--white);">{{ $resultado->piloto->nombre }}</td>
                        <td style="text-align: center; color: var(--gray);">{{ $resultado->vueltas ?? '-' }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->tiempo_total_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->mejor_tiempo_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->diferencia_primero_formateada }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_1_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_2_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_3_formateado }}</td>
                    </tr>
                    @endforeach

                    @if($noClasificados->count() > 0)
                    <tr>
                        <td colspan="10" style="background: #111; color: var(--gray); font-size: 11px; text-transform: uppercase; padding: 6px 16px;">
                            No clasificado
                        </td>
                    </tr>
                    @foreach($noClasificados as $resultado)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--racing);">
                            {{ $resultado->excluido ? 'EX' : 'NT' }}
                        </td>
                        <td style="text-align: center; color: var(--gray);">
                            {{ $resultado->piloto->numero_auto_pivot ?? '-' }}
                        </td>
                        <td style="font-weight: 500; color: var(--racing);">{{ $resultado->piloto->nombre }}</td>
                        <td style="text-align: center; color: var(--gray);">{{ $resultado->vueltas ?? '-' }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->tiempo_total_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--bone);">{{ $resultado->mejor_tiempo_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--racing);">{{ $resultado->excluido ? 'EX' : 'NT' }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_1_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_2_formateado }}</td>
                        <td style="text-align: right; font-family: var(--font-display); color: var(--gray); font-size: 11px;">{{ $resultado->sector_3_formateado }}</td>
                    </tr>
                    @endforeach
                    @endif

                </tbody>
                @endif
            </table>
        @endif
    </div>
    @endforeach
@endif

@include('components.admin.delete-modal')

<script>
function switchTab(tabId, btn) {
    // Hide all panes
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    // Show target pane
    document.getElementById(tabId).style.display = 'block';
    
    // Remove active class from all buttons
    document.querySelectorAll('.resultados-tabs .tab-btn').forEach(el => {
        el.classList.remove('active');
    });
    
    // Add active class to clicked button
    btn.classList.add('active');
}
</script>

@endsection
