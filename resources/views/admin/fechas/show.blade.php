@extends('layouts.admin')

@section('title', 'Ver Fecha')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información de la Fecha',
        'fields' => [
            [
                'label' => 'Nombre de la Fecha',
                'type' => 'text',
                'value' => $fecha->nombre,
                'width' => 8,
                'icon' => 'calendar-days'
            ],
            [
                'label' => 'Duración',
                'type' => 'text',
                'value' => $fecha->fecha_desde && $fecha->fecha_hasta 
                    ? \Carbon\Carbon::parse($fecha->fecha_desde)->diffInDays(\Carbon\Carbon::parse($fecha->fecha_hasta)) + 1 . ' días'
                    : '—',
                'width' => 4,
                'icon' => 'clock'
            ],
            [
                'label' => 'Fecha Desde',
                'type' => 'date',
                'value' => $fecha->fecha_desde,
                'format' => 'd/m/Y',
                'showRelative' => true,
                'width' => 6,
                'icon' => 'calendar-days'
            ],
            [
                'label' => 'Fecha Hasta',
                'type' => 'date',
                'value' => $fecha->fecha_hasta,
                'format' => 'd/m/Y',
                'showRelative' => true,
                'width' => 6,
                'icon' => 'calendar-days'
            ],
            [
                'label' => 'Circuito',
                'type' => 'text',
                'value' => $fecha->circuito->nombre ?? '—',
                'width' => 6,
                'icon' => 'map'
            ],
            [
                'label' => 'Campeonato',
                'type' => 'text',
                'value' => $fecha->campeonato->nombre ?? '—',
                'width' => 6,
                'icon' => 'trophy'
            ],
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.fechas.edit', $fecha),
                'class' => 'primary',
                'icon' => 'pencil-square'
            ],
            [
                'type' => 'link',
                'label' => 'Resultados',
                'route' => route('admin.fechas.resultados', $fecha),
                'class' => 'primary',
                'icon' => 'list-bullet'
            ],
            [
                'type' => 'button',
                'label' => 'Eliminar',
                'icon' => 'trash',
                'class' => 'destructive',
                'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#deleteFechaModal',
                    'delete-url' => route('admin.fechas.destroy', $fecha),
                    'item-name' => $fecha->nombre
                ]
            ],
            [
                'type' => 'link',
                'label' => $fecha->sistemaPuntaje()->exists() ? 'Puntaje personalizado' : 'Personalizar puntaje',
                'route' => route('admin.fechas.scoring', $fecha),
                'class' => 'ghost',
                'icon' => 'adjustments-horizontal'
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.fechas.index'),
                'class' => 'ghost',
                'icon' => 'arrow-left'
            ]
        ]
    ])

{{-- Session Action Buttons --}}
<div style="margin-top: 16px; display: flex; gap: 12px; flex-wrap: wrap;">
    <a href="{{ route('admin.sesiones.create') }}?fecha_id={{ $fecha->id }}" class="btn" style="background: var(--white); color: var(--black);">
        <x-heroicon-o-plus style="width:1em; height:1em; vertical-align:-0.125em;" /> Agregar Sesión
    </a>
    
    @if($sesionesPendientes > 0)
    <button type="button" class="btn ghost" data-bs-toggle="modal" data-bs-target="#generarSesionesModal">
        <x-heroicon-o-sparkles style="width:1em; height:1em; vertical-align:-0.125em;" /> Crear Sesiones Estándar
        @if($fecha->sesiones->isNotEmpty())
            <span class="badge" style="background: var(--carbon); color: var(--bone); border: 1px solid var(--line); margin-left: 8px;">{{ $sesionesPendientes }} pendientes</span>
        @endif
    </button>
    @endif

    @if($fecha->sesiones->isNotEmpty())
    <button type="button" class="btn danger" data-bs-toggle="modal" data-bs-target="#eliminarSesionesModal">
        <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" /> Eliminar Sesiones
    </button>
    @endif
</div>

{{-- Modal Confirmar Crear Sesiones --}}
@if($sesionesPendientes > 0)
<div class="custom-modal" id="generarSesionesModal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <x-heroicon-o-sparkles style="width:1em; height:1em; vertical-align:-0.125em; color: var(--white); margin-right: 8px;" /> Crear Sesiones Estándar
            </h5>
            <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p style="margin-bottom: 12px; font-size: 15px;">¿Querés generar automáticamente las siguientes sesiones para <strong>{{ $fecha->nombre }}</strong>?</p>
            <div style="background-color: var(--carbon-2); border: 1px dashed var(--gray); padding: 12px; margin-top: 16px;">
                <ul style="margin: 0; padding-left: 20px; color: var(--bone); font-size: 13px; line-height: 1.6;">
                    @foreach($sesionesPendientesLista as $nombre)
                        <li>{{ $nombre }}</li>
                    @endforeach
                </ul>
            </div>
            <div style="margin-top: 16px; color: var(--gray); font-size: 13px;">
                <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em;" /> Se crearán con sus horarios predefinidos basándose en los días de la fecha.
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn ghost" data-dismiss="modal">
                Cancelar
            </button>
            <button type="button" class="btn" style="background: var(--white); color: var(--black);" id="confirmGenerarSesiones">
                <x-heroicon-o-check style="width:1em; height:1em; vertical-align:-0.125em;" /> Confirmar
            </button>
        </div>
    </div>
</div>

<form id="generarSesionesForm" method="POST" action="{{ route('admin.fechas.generar-sesiones', $fecha) }}" style="display: none;">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmGenerarSesiones');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            this.innerHTML = 'Generando...';
            this.disabled = true;
            document.getElementById('generarSesionesForm').submit();
        });
    }
});
</script>
@endif

{{-- Modal Confirmar Eliminar Sesiones --}}
@if($fecha->sesiones->isNotEmpty())
<div class="custom-modal" id="eliminarSesionesModal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <x-heroicon-o-exclamation-triangle style="width:1em; height:1em; vertical-align:-0.125em; color: var(--racing); margin-right: 8px;" /> Eliminar Sesiones
            </h5>
            <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p style="margin-bottom: 12px; font-size: 15px;">¿Estás seguro de que querés eliminar <strong>todas las sesiones y horarios</strong> de <strong>{{ $fecha->nombre }}</strong>?</p>
            <div style="background-color: rgba(229, 9, 20, 0.1); border: 1px dashed var(--racing); padding: 12px; margin-top: 16px;">
                <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em; color: var(--racing); margin-right: 8px;" />
                <small style="color: var(--bone);">
                    Esta acción eliminará {{ $fecha->sesiones->count() }} sesiones y sus horarios. No se puede deshacer.
                </small>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn ghost" data-dismiss="modal">
                Cancelar
            </button>
            <button type="button" class="btn" style="border-color: var(--racing); color: var(--racing);" id="confirmEliminarSesiones">
                <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" /> Eliminar Todo
            </button>
        </div>
    </div>
</div>

<form id="eliminarSesionesForm" method="POST" action="{{ route('admin.fechas.eliminar-sesiones', $fecha) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmEliminarBtn = document.getElementById('confirmEliminarSesiones');
    if (confirmEliminarBtn) {
        confirmEliminarBtn.addEventListener('click', function() {
            this.innerHTML = 'Eliminando...';
            this.disabled = true;
            document.getElementById('eliminarSesionesForm').submit();
        });
    }
});
</script>
@endif

@include('components.admin.delete-modal', [
    'modalId' => 'deleteFechaModal',
    'title' => 'Confirmar Eliminación de Fecha',
    'message' => '¿Estás seguro de que deseas eliminar la fecha',
    'warningText' => 'Esta acción eliminará permanentemente la fecha y no se puede deshacer.',
    'confirmText' => 'Eliminar Fecha',
    'cancelText' => 'Cancelar'
])

{{-- Tabla de Sesiones y Horarios --}}
@if($fecha->sesiones->isNotEmpty())
<div class="tbl-wrap" style="margin-top: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--black); border-bottom: 1px solid var(--carbon);">
        <h5 style="margin: 0; font-family: var(--font-oswald); font-size: 16px; text-transform: uppercase;">
            <x-heroicon-o-clock style="width:1em; height:1em; vertical-align:-0.125em; margin-right: 8px;" /> Sesiones y Horarios
        </h5>
        <span style="font-size: 12px; color: var(--gray); font-family: var(--font-sans);">
            {{ $fecha->sesiones->count() }} SESIONES
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%">Día</th>
                <th style="width: 25%">Sesión</th>
                <th style="width: 10%; text-align: center;">Hora</th>
                <th style="width: 15%; text-align: center;">Duración</th>
                <th style="width: 20%">Observaciones</th>
                <th style="width: 15%; text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fecha->sesiones->sortBy(function($s) {
                return \App\Models\SesionDefinicion::ORDEN[$s->tipo] ?? 99;
            }) as $sesion)
            @php $horario = $sesion->horarios->first(); @endphp
            <tr>
                <td style="color: var(--gray);">
                    {{ $sesion->fecha_sesion ? \Carbon\Carbon::parse($sesion->fecha_sesion)->format('d/m/Y') : '—' }}
                </td>
                <td style="font-weight: 500; color: var(--bone);">
                    {{ $sesion->tipoNombre }}
                </td>
                <td style="text-align: center; color: var(--white); font-family: var(--font-display);">
                    {{ $horario ? \Carbon\Carbon::parse($horario->horario)->format('H:i') : '—' }}
                </td>
                <td style="text-align: center; color: var(--gray);">
                    {{ $horario?->duracion ?? '—' }}
                </td>
                <td style="color: var(--gray); font-size: 12px;">
                    {{ $horario?->observaciones ?: '—' }}
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn ghost" style="padding: 6px 12px; min-width: 0;"
                        title="Editar" data-bs-toggle="modal" data-bs-target="#editSesionModal"
                        data-sesion-id="{{ $sesion->id }}"
                        data-horario-id="{{ $horario?->id }}"
                        data-horario-url="{{ $horario ? route('admin.horarios.update-from-fecha', $horario) : '' }}"
                        data-fecha-sesion="{{ $sesion->fecha_sesion ?? '' }}"
                        data-hora="{{ $horario ? \Carbon\Carbon::parse($horario->horario)->format('H:i') : '' }}"
                        data-duracion="{{ $horario->duracion ?? '' }}"
                        data-observaciones="{{ $horario->observaciones ?? '' }}"
                        data-label="{{ $sesion->tipoNombre }}">
                        <x-heroicon-o-pencil-square style="width:1em; height:1em; vertical-align:-0.125em;" />
                    </button>
                    <button type="button" class="btn danger" style="padding: 6px 12px; min-width: 0;"
                        title="Eliminar" data-bs-toggle="modal" data-bs-target="#deleteModal"
                        data-delete-url="{{ route('admin.sesiones.destroy', $sesion) }}"
                        data-item-name="la sesión de {{ $sesion->tipoNombre }}">
                        <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" />
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal Editar Sesión --}}
<div class="custom-modal" id="editSesionModal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <x-heroicon-o-pencil-square style="width:1em; height:1em; vertical-align:-0.125em; color: var(--white); margin-right: 8px;" /> Editar Sesión: <span id="editSesionNombre"></span>
            </h5>
            <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
        </div>
        <form id="editSesionForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="custom-modal-body">
                <div class="fgrid">
                    <div class="field" style="grid-column: span 6;">
                        <label>Fecha de la sesión</label>
                        <input type="date" name="fecha_sesion" id="editFechaSesion" required style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                    </div>
                    <div class="field" style="grid-column: span 6;">
                        <label>Hora de inicio</label>
                        <input type="time" name="horario" id="editHora" required style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                    </div>
                    <div class="field full">
                        <label>Duración</label>
                        <input type="text" name="duracion" id="editDuracion" placeholder="Ej: 15 min, 6 vueltas" style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                    </div>
                    <div class="field full">
                        <label>Observaciones</label>
                        <textarea name="observaciones" id="editObservaciones" rows="2" style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);"></textarea>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn ghost" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn" style="background: var(--white); color: var(--black);">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editSesionModal');
    
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-bs-target="#editSesionModal"]');
        if (toggleBtn) {
            document.getElementById('editSesionNombre').textContent = toggleBtn.dataset.label;
            document.getElementById('editFechaSesion').value = toggleBtn.dataset.fechaSesion;
            document.getElementById('editHora').value = toggleBtn.dataset.hora;
            document.getElementById('editDuracion').value = toggleBtn.dataset.duracion;
            document.getElementById('editObservaciones').value = toggleBtn.dataset.observaciones;
            document.getElementById('editSesionForm').action = toggleBtn.dataset.horarioUrl;
            
            if (editModal) editModal.classList.add('show');
        }

        const dismissBtn = e.target.closest('#editSesionModal [data-dismiss="modal"]');
        if (dismissBtn && editModal) {
            editModal.classList.remove('show');
        }
    });

    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                editModal.classList.remove('show');
            }
        });
    }
});
</script>
@endif

@include('components.admin.delete-modal')

@endsection