@extends('layouts.admin')

@section('content')
<div class="view-head">
    <h1>PUNTAJE: {{ strtoupper($fecha->nombre) }}</h1>
</div>

@if($scoring->isEmpty())
<div class="form-card text-center" style="max-width: 600px; padding: 48px 32px;">
    <div style="margin-bottom: 24px;">
        <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em; font-size: 48px; color: var(--racing);" />
    </div>
    <h5 style="font-family: var(--font-oswald); font-size: 24px; color: var(--white); text-transform: uppercase; margin-bottom: 12px;">Usando puntaje del campeonato</h5>
    <p style="color: var(--gray); font-size: 14px; margin-bottom: 32px; line-height: 1.6;">
        Esta fecha sigue las reglas generales del campeonato. Podés verlas en el sistema de puntaje general o personalizarlas solo para esta fecha.
    </p>
    <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="{{ route('admin.campeonatos.scoring', $fecha->campeonato_id) }}" class="btn ghost">
            <x-heroicon-o-eye style="width:1em; height:1em; vertical-align:-0.125em;" /> Ver reglas generales
        </a>
        <form method="POST" action="{{ route('admin.fechas.scoring.customize', $fecha) }}">
            @csrf
            <button type="submit" class="btn" style="background: var(--white); color: var(--black);">
                <x-heroicon-o-pencil-square style="width:1em; height:1em; vertical-align:-0.125em;" /> Personalizar para esta fecha
            </button>
        </form>
    </div>
</div>
@else
<div class="form-actions" style="justify-content: flex-start; gap: 12px; margin-bottom: 24px;">
    <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn ghost">
        <x-heroicon-o-arrow-left style="width:1em; height:1em; vertical-align:-0.125em;" /> Volver a la Fecha
    </a>
    <form method="POST" action="{{ route('admin.fechas.scoring.reset', $fecha) }}"
          onsubmit="return confirm('¿Eliminar el puntaje personalizado y volver al puntaje del campeonato?')">
        @csrf
        <button type="submit" class="btn" style="color: var(--racing); border-color: var(--racing);">
            <x-heroicon-o-arrow-uturn-left style="width:1em; height:1em; vertical-align:-0.125em;" /> Eliminar personalización
        </button>
    </form>
</div>

<div style="background-color: rgba(229, 9, 20, 0.1); border-left: 3px solid var(--racing); padding: 16px; margin-bottom: 32px; font-family: var(--font-sans);">
    <p style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: var(--white);">
        <x-heroicon-o-star style="width:1em; height:1em; vertical-align:-0.125em; color: var(--racing); margin-right: 8px;" />Puntaje personalizado activo para esta fecha
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

<form method="POST" id="bulk-scoring-form" action="{{ route('admin.fechas.scoring.bulk', $fecha) }}">
    @csrf

@foreach($tipoOrder as $tipo)
<div class="tbl-wrap" style="margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--black); border-bottom: 1px solid var(--carbon);">
        <h5 style="margin: 0; font-family: var(--font-oswald); font-size: 18px; text-transform: uppercase;">
            @php
                $icon = match($tipo) {
                    'presentacion'  => 'identification',
                    'clasificacion' => 'clock',
                    'serie'         => 'list-bullet',
                    'final'         => 'flag',
                    default         => 'star',
                };
            @endphp
            <x-dynamic-component :component="'heroicon-o-' . $icon" style="width:1em; height:1em; vertical-align:-0.125em; margin-right: 8px; color: var(--gray);" /> {{ $tipoLabels[$tipo] ?? $tipo }}
        </h5>
        @if($tipo !== 'presentacion')
        <button type="button" class="btn ghost" style="padding: 6px 12px; font-size: 12px; min-width: auto;" onclick="addScoringRow('{{ $tipo }}')">
            <x-heroicon-o-plus style="width:1em; height:1em; vertical-align:-0.125em;" /> Agregar posición
        </button>
        @endif
    </div>

    <table id="table-{{ $tipo }}">
        <thead>
            <tr>
                <th style="width: 50%;">Condición</th>
                <th style="width: 30%;">Puntos</th>
                <th style="width: 20%; text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody id="tbody-{{ $tipo }}">
            @if(isset($scoring[$tipo]))
            @foreach($scoring[$tipo] as $index => $row)
            <tr id="row-{{ $tipo }}-{{ $index }}">
                <td style="font-weight: 500; color: var(--bone);">
                    @if($tipo === 'presentacion') 
                        Presencia en la fecha
                        <input type="hidden" name="scoring[{{ $tipo }}][{{ $index }}][posicion]" value="1">
                    @else 
                        <input type="number" name="scoring[{{ $tipo }}][{{ $index }}][posicion]" value="{{ $row->posicion }}" min="1" max="999" required style="width: 80px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 6px; font-family: var(--font-sans);">
                        ° puesto
                    @endif
                </td>
                <td>
                    <input type="number" name="scoring[{{ $tipo }}][{{ $index }}][puntos]" value="{{ $row->puntos }}" min="0" max="9999" required style="width: 100px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 6px; font-family: var(--font-sans);">
                </td>
                <td style="text-align: center;">
                    @if($tipo !== 'presentacion')
                    <button type="button" class="btn danger" onclick="removeScoringRow('row-{{ $tipo }}-{{ $index }}')" style="padding: 6px 12px; font-size: 12px; min-width: auto;">
                        <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" />
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
            @else
            <tr id="empty-{{ $tipo }}">
                <td colspan="3" style="text-align: center; color: var(--gray); padding: 32px;">Sin filas configuradas.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endforeach

    <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
        <button type="submit" class="btn" style="background: var(--white); color: var(--black); font-size: 16px; padding: 12px 24px;">
            <x-heroicon-o-document-check style="width:1em; height:1em; vertical-align:-0.125em;" /> GUARDAR SISTEMA DE PUNTAJE
        </button>
    </div>
</form>

@push('scripts')
<script>
    let rowCounter = 999;
    
    function addScoringRow(tipo) {
        rowCounter++;
        const tbody = document.getElementById('tbody-' + tipo);
        const emptyRow = document.getElementById('empty-' + tipo);
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }
        
        const tr = document.createElement('tr');
        tr.id = 'row-' + tipo + '-' + rowCounter;
        
        let conditionHtml = `
            <input type="number" name="scoring[${tipo}][${rowCounter}][posicion]" placeholder="Pos" min="1" max="999" required style="width: 80px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 6px; font-family: var(--font-sans);">
            ° puesto
        `;
        
        tr.innerHTML = `
            <td style="font-weight: 500; color: var(--bone);">
                ${conditionHtml}
            </td>
            <td>
                <input type="number" name="scoring[${tipo}][${rowCounter}][puntos]" placeholder="Pts" min="0" max="9999" required style="width: 100px; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 6px; font-family: var(--font-sans);">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn danger" onclick="removeScoringRow('${tr.id}')" style="padding: 6px 12px; font-size: 12px; min-width: auto;">
                    <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" />
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    }
    
    function removeScoringRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    }
</script>
@endpush
@endsection
