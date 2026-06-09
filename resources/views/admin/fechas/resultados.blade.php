@extends('layouts.admin')

@section('title', 'Resultados Completos de ' . $fecha->nombre)

@section('content')
<div class="view-head">
    <h1>RESULTADOS: {{ strtoupper($fecha->nombre) }} <span class="lap">{{ strtoupper($fecha->circuito->nombre ?? 'SIN CIRCUITO') }}</span></h1>
</div>

<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn ghost">
        <i class="fas fa-arrow-left"></i> Volver a la Fecha
    </a>
    <a href="{{ route('admin.resultados.index', ['fecha_id' => $fecha->id]) }}" class="btn ghost">
        <i class="fas fa-list"></i> Administrar Resultados Indivíduales
    </a>
    <form method="POST" action="{{ route('admin.fechas.generar-acumulados', $fecha) }}" style="display: inline-block;">
        @csrf
        <button type="submit" class="btn" style="background: var(--white); color: var(--black);">
            <i class="fas fa-calculator"></i> Generar Acumulados
        </button>
    </form>
</div>

@empty($fecha->sesiones)
    <div class="form-card text-center" style="padding: 40px; color: var(--gray);">
        <i class="fas fa-info-circle" style="font-size: 32px; margin-bottom: 16px;"></i>
        <p style="margin: 0;">No hay sesiones registradas para esta fecha.</p>
    </div>
@else
    @foreach($fecha->sesiones as $sesion)
    <div class="tbl-wrap" style="margin-bottom: 32px;">
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
                    <i class="fas fa-trash-alt"></i> Borrar Sesión
                </button>
                @endif
                <span style="font-size: 12px; color: var(--gray); font-family: var(--font-sans);">
                    <i class="fas fa-flag-checkered" style="margin-right: 4px;"></i> {{ count($sesion->resultados) }} PILOTOS
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

@endsection
