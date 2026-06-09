@extends('layouts.admin')
@section('title', 'Sistema de Puntaje — ' . $campeonato->nombre)

@section('content')
<div class="view-head">
    <h1>SISTEMA DE PUNTAJE <span class="lap">{{ strtoupper($campeonato->nombre) }}</span></h1>
</div>

<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.campeonatos.standings', $campeonato) }}" class="btn ghost">
        <i class="fas fa-arrow-left"></i> Ver Clasificación
    </a>
    <form method="POST" action="{{ route('admin.campeonatos.scoring.reset', $campeonato) }}"
          onsubmit="return confirm('¿Restablecer el sistema de puntaje a los valores predeterminados del reglamento?')">
        @csrf
        <button type="submit" class="btn" style="color: var(--racing); border-color: var(--racing);">
            <i class="fas fa-rotate-left"></i> Restablecer predeterminados
        </button>
    </form>
</div>

{{-- Reglas de puntaje --}}
<div style="background-color: var(--carbon-2); border-left: 3px solid var(--racing); padding: 16px; margin-bottom: 32px; font-family: var(--font-sans);">
    <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: var(--white);">
        <i class="fas fa-info-circle" style="color: var(--racing); margin-right: 8px;"></i>Reglas de cálculo aplicadas
    </p>
    <ul style="margin: 0; padding-left: 20px; color: var(--bone); font-size: 13px; line-height: 1.6;">
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
        <form method="POST" action="{{ route('admin.campeonatos.scoring.add', $campeonato) }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
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
                    <form method="POST" id="update-form-{{ $row->id }}" action="{{ route('admin.campeonatos.scoring.update', [$campeonato, $row]) }}">
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
                    <form method="POST" action="{{ route('admin.campeonatos.scoring.delete', [$campeonato, $row]) }}" onsubmit="return confirm('¿Eliminar el puesto {{ $row->posicion }}?')">
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
