@extends('layouts.admin')
@section('title', 'Puntaje de Fecha — ' . $fecha->nombre)

@section('content')
<div class="view-head">
    <h1>PUNTAJE: {{ strtoupper($fecha->nombre) }} <span class="lap">CAMPEONATO: {{ strtoupper($fecha->campeonato->nombre) }}</span></h1>
</div>

@if($scoring->isEmpty())
<div class="form-card text-center" style="max-width: 600px; padding: 48px 32px;">
    <div style="margin-bottom: 24px;">
        <i class="fas fa-info-circle" style="font-size: 48px; color: var(--racing);"></i>
    </div>
    <h5 style="font-family: var(--font-oswald); font-size: 24px; color: var(--white); text-transform: uppercase; margin-bottom: 12px;">Usando puntaje del campeonato</h5>
    <p style="color: var(--gray); font-size: 14px; margin-bottom: 32px; line-height: 1.6;">
        Esta fecha sigue las reglas generales del campeonato. Podés verlas en el sistema de puntaje general o personalizarlas solo para esta fecha.
    </p>
    <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="{{ route('admin.campeonatos.scoring', $fecha->campeonato_id) }}" class="btn ghost">
            <i class="fas fa-eye"></i> Ver reglas generales
        </a>
        <form method="POST" action="{{ route('admin.fechas.scoring.customize', $fecha) }}">
            @csrf
            <button type="submit" class="btn" style="background: var(--white); color: var(--black);">
                <i class="fas fa-edit"></i> Personalizar para esta fecha
            </button>
        </form>
    </div>
</div>
@else
<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn ghost">
        <i class="fas fa-arrow-left"></i> Volver a la Fecha
    </a>
    <form method="POST" action="{{ route('admin.fechas.scoring.reset', $fecha) }}"
          onsubmit="return confirm('¿Eliminar el puntaje personalizado y volver al puntaje del campeonato?')">
        @csrf
        <button type="submit" class="btn" style="color: var(--racing); border-color: var(--racing);">
            <i class="fas fa-rotate-left"></i> Eliminar personalización
        </button>
    </form>
</div>

<div style="background-color: rgba(229, 9, 20, 0.1); border-left: 3px solid var(--racing); padding: 16px; margin-bottom: 32px; font-family: var(--font-sans);">
    <p style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: var(--white);">
        <i class="fas fa-star" style="color: var(--racing); margin-right: 8px;"></i>Puntaje personalizado activo para esta fecha
    </p>
    <p style="margin: 0; font-size: 13px; color: var(--gray);">
        Este puntaje sobreescribe al del campeonato <strong>únicamente para esta fecha</strong>.
    </p>
</div>
@endif

@php
    $tipoLabels = \App\Models\SistemaPuntaje::TIPO_LABELS;
    $tipoOrder  = ['presentacion', 'clasificacion', 'serie', 'final'];
@endphp

@foreach($tipoOrder as $tipo)
<div class="tbl-wrap" style="margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--black); border-bottom: 1px solid var(--carbon);">
        <h5 style="margin: 0; font-family: var(--font-oswald); font-size: 18px; text-transform: uppercase;">
            @php
                $icon = match($tipo) {
                    'presentacion'  => 'fa-id-card-clip',
                    'clasificacion' => 'fa-stopwatch',
                    'serie'         => 'fa-list-ol',
                    'final'         => 'fa-flag-checkered',
                    default         => 'fa-star',
                };
            @endphp
            <i class="fas {{ $icon }}" style="margin-right: 8px; color: var(--gray);"></i> {{ $tipoLabels[$tipo] ?? $tipo }}
        </h5>
        @if($tipo !== 'presentacion')
        <button class="btn ghost" style="padding: 6px 12px; font-size: 12px; min-width: auto;" onclick="document.getElementById('addRow-{{ $tipo }}').style.display = document.getElementById('addRow-{{ $tipo }}').style.display === 'none' ? 'flex' : 'none';">
            <i class="fas fa-plus"></i> Agregar posición
        </button>
        @endif
    </div>

    @if($tipo !== 'presentacion')
    <div id="addRow-{{ $tipo }}" style="display: none; padding: 16px; background: #131313; border-bottom: 1px solid var(--carbon);">
        <form method="POST" action="{{ route('admin.fechas.scoring.add', $fecha) }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <input type="hidden" name="tipo_sesion" value="{{ $tipo }}">
            <div style="display: flex; flex-direction: column;">
                <label style="color: var(--gray); font-size: 12px; margin-bottom: 6px;">Posición</label>
                <input type="number" name="posicion" placeholder="Ej: 21" min="1" max="999" required style="width: 100px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label style="color: var(--gray); font-size: 12px; margin-bottom: 6px;">Puntos</label>
                <input type="number" name="puntos" placeholder="0" min="0" max="9999" required style="width: 100px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
            </div>
            <button type="submit" class="btn" style="background: var(--white); color: var(--black); padding: 8px 16px; min-width: auto; height: 37px;">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </form>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Condición</th>
                <th style="width: 30%;">Puntos</th>
                <th style="width: 15%; text-align: center;">Guardar</th>
                <th style="width: 15%; text-align: center;">Borrar</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($scoring[$tipo]))
            @foreach($scoring[$tipo] as $row)
            <tr>
                <td style="font-weight: 500; color: var(--bone);">
                    @if($tipo === 'presentacion') Presencia en la fecha
                    @elseif($tipo === 'clasificacion') {{ $row->posicion }}° puesto (Pole)
                    @else {{ $row->posicion }}° puesto
                    @endif
                </td>
                <td>
                    <form method="POST" id="update-form-{{ $row->id }}" action="{{ route('admin.fechas.scoring.update', [$fecha, $row]) }}">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="puntos" value="{{ $row->puntos }}" min="0" max="9999" style="width: 80px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 6px; font-family: var(--font-sans);">
                    </form>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn ghost" onclick="document.getElementById('update-form-{{ $row->id }}').submit();" style="padding: 6px 12px; font-size: 12px; min-width: auto;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </td>
                <td style="text-align: center;">
                    @if($tipo !== 'presentacion')
                    <form method="POST" action="{{ route('admin.fechas.scoring.delete', [$fecha, $row]) }}" onsubmit="return confirm('¿Eliminar el puesto {{ $row->posicion }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn ghost" style="padding: 6px 12px; font-size: 12px; min-width: auto; color: var(--racing); border-color: transparent;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="4" style="text-align: center; color: var(--gray); padding: 32px;">Sin filas configuradas.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endforeach

@endsection
