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
                'icon' => 'fas fa-calendar-alt'
            ],
            [
                'label' => 'Duración',
                'type' => 'text',
                'value' => $fecha->fecha_desde && $fecha->fecha_hasta 
                    ? \Carbon\Carbon::parse($fecha->fecha_desde)->diffInDays(\Carbon\Carbon::parse($fecha->fecha_hasta)) + 1 . ' días'
                    : '—',
                'width' => 4,
                'icon' => 'fas fa-clock'
            ],
            [
                'label' => 'Fecha Desde',
                'type' => 'date',
                'value' => $fecha->fecha_desde,
                'format' => 'd/m/Y',
                'showRelative' => true,
                'width' => 6,
                'icon' => 'fas fa-calendar-plus'
            ],
            [
                'label' => 'Fecha Hasta',
                'type' => 'date',
                'value' => $fecha->fecha_hasta,
                'format' => 'd/m/Y',
                'showRelative' => true,
                'width' => 6,
                'icon' => 'fas fa-calendar-minus'
            ],
            [
                'label' => 'Circuito',
                'type' => 'text',
                'value' => $fecha->circuito->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-road'
            ],
            [
                'label' => 'Campeonato',
                'type' => 'text',
                'value' => $fecha->campeonato->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-trophy'
            ],
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.fechas.edit', $fecha),
                'class' => 'btn-primary-modern',
                'icon' => 'fas fa-edit'
            ],
            [
                'type' => 'link',
                'label' => 'Resultados',
                'route' => route('admin.fechas.resultados', $fecha),
                'class' => 'btn-primary-modern',
                'icon' => 'fas fa-list-ol'
            ],
            [
                'type' => 'button',
                'label' => 'Eliminar',
                'icon' => 'fas fa-trash',
                'class' => 'btn-destructive-modern',
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
                'class' => $fecha->sistemaPuntaje()->exists() ? 'btn-primary-modern' : 'btn-secondary-modern',
                'icon' => 'fas fa-sliders'
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.fechas.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Session Action Buttons --}}
<div class="mt-3 d-flex gap-2">
    @if($sesionesPendientes > 0)
    <button type="button" class="btn-modern btn-secondary-modern" data-bs-toggle="modal" data-bs-target="#generarSesionesModal">
        <i class="fas fa-magic me-2"></i> Crear Sesiones Estándar
        @if($fecha->sesiones->isNotEmpty())
            <span class="badge bg-light text-dark ms-1">{{ $sesionesPendientes }} pendientes</span>
        @endif
    </button>
    @endif

    @if($fecha->sesiones->isNotEmpty())
    <button type="button" class="btn-modern btn-destructive-modern" data-bs-toggle="modal" data-bs-target="#eliminarSesionesModal">
        <i class="fas fa-trash me-2"></i> Eliminar Sesiones
    </button>
    @endif
</div>

{{-- Modal Confirmar Crear Sesiones --}}
@if($sesionesPendientes > 0)
<div class="modal fade" id="generarSesionesModal" tabindex="-1" aria-labelledby="generarSesionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-danger">
            <div class="modal-header">
                <h5 class="modal-title" id="generarSesionesModalLabel">
                    <i class="fas fa-magic me-2"></i> Crear Sesiones Estándar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Querés generar automáticamente las siguientes sesiones para <strong>{{ $fecha->nombre }}</strong>?</p>
                <div class="p-3 rounded mb-3" style="background-color: var(--secondary-bg); border: 1px dashed var(--border-color);">
                    <ul class="mb-0 ps-3">
                        @foreach($sesionesPendientesLista as $nombre)
                            <li class="small fw-medium">{{ $nombre }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="d-flex align-items-center p-3 rounded" style="background-color: hsl(var(--accent) / 0.1); border: 1px solid hsl(var(--accent) / 0.3);">
                    <i class="fas fa-info-circle me-2" style="color: hsl(var(--accent));"></i>
                    <small style="color: hsl(var(--foreground));">
                        Se crearán con sus horarios predefinidos basándose en los días de la fecha.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-modern btn-primary-modern" id="confirmGenerarSesiones">
                    <i class="fas fa-magic me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Formulario oculto para generar sesiones --}}
<form id="generarSesionesForm" method="POST" action="{{ route('admin.fechas.generar-sesiones', $fecha) }}" style="display: none;">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmGenerarSesiones');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="me-1"></span>Generando...';
            this.disabled = true;
            document.getElementById('generarSesionesForm').submit();
        });
    }
});
</script>
@endif

{{-- Modal Confirmar Eliminar Sesiones --}}
@if($fecha->sesiones->isNotEmpty())
<div class="modal fade" id="eliminarSesionesModal" tabindex="-1" aria-labelledby="eliminarSesionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-danger">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarSesionesModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Eliminar Sesiones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Estás seguro de que querés eliminar <strong>todas las sesiones y horarios</strong> de <strong>{{ $fecha->nombre }}</strong>?</p>
                <div class="d-flex align-items-center p-3 rounded" style="background-color: hsl(var(--destructive) / 0.1); border: 1px solid hsl(var(--destructive) / 0.2);">
                    <i class="fas fa-info-circle me-2" style="color: hsl(var(--destructive));"></i>
                    <small style="color: hsl(var(--destructive));">
                        Esta acción eliminará {{ $fecha->sesiones->count() }} sesiones y sus horarios. No se puede deshacer.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-modern btn-destructive-modern" id="confirmEliminarSesiones">
                    <i class="fas fa-trash me-1"></i> Eliminar Todo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Formulario oculto para eliminar sesiones --}}
<form id="eliminarSesionesForm" method="POST" action="{{ route('admin.fechas.eliminar-sesiones', $fecha) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmEliminarBtn = document.getElementById('confirmEliminarSesiones');
    if (confirmEliminarBtn) {
        confirmEliminarBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="me-1"></span>Eliminando...';
            this.disabled = true;
            document.getElementById('eliminarSesionesForm').submit();
        });
    }
});
</script>
@endif


{{-- Modal de Confirmación de Eliminación --}}
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
<div class="card-modern mt-4">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-clock me-2"></i> Sesiones y Horarios
        </h5>
        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
            {{ $fecha->sesiones->count() }} sesiones
        </span>
    </div>
    <div class="card-body-modern p-0">
        <div class="table-responsive">
            <table class="table-modern table-modern-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 18%">Día</th>
                        <th style="width: 28%">Sesión</th>
                        <th style="width: 13%" class="text-center">Hora</th>
                        <th style="width: 14%" class="text-center">Duración</th>
                        <th style="width: 19%">Observaciones</th>
                        <th style="width: 8%" class="text-center">Editar</th>
                        <th style="width: 8%" class="text-center">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fecha->sesiones->sortBy(function($s) {
                        return \App\Models\SesionDefinicion::ORDEN[$s->tipo] ?? 99;
                    }) as $sesion)
                    @php $horario = $sesion->horarios->first(); @endphp
                    <tr>
                        <td class="text-muted" style="font-size: 0.85rem;">
                            {{ $sesion->fecha_sesion ? \Carbon\Carbon::parse($sesion->fecha_sesion)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="fw-medium">
                            {{ $sesion->tipoNombre }}
                        </td>
                        <td class="text-center font-monospace">
                            {{ $horario ? \Carbon\Carbon::parse($horario->horario)->format('H:i') : '—' }}
                        </td>
                        <td class="text-center text-muted">
                            {{ $horario?->duracion ?? '—' }}
                        </td>
                        <td class="text-muted" style="font-size: 0.875rem;">
                            {{ $horario?->observaciones ?: '—' }}
                        </td>
                        <td class="text-center">
                            <button type="button"
                                class="btn-modern btn-secondary-modern p-2"
                                style="width: 32px; height: 32px;"
                                title="Editar"
                                data-bs-toggle="modal"
                                data-bs-target="#editSesionModal"
                                data-sesion-id="{{ $sesion->id }}"
                                data-horario-id="{{ $horario?->id }}"
                                data-horario-url="{{ $horario ? route('admin.horarios.update-from-fecha', $horario) : '' }}"
                                data-fecha-sesion="{{ $sesion->fecha_sesion ?? '' }}"
                                data-hora="{{ $horario ? \Carbon\Carbon::parse($horario->horario)->format('H:i') : '' }}"
                                data-duracion="{{ $horario->duracion ?? '' }}"
                                data-observaciones="{{ $horario->observaciones ?? '' }}"
                                data-label="{{ $sesion->tipoNombre }}">
                                <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                class="btn-modern btn-destructive-modern p-2"
                                style="width: 32px; height: 32px;"
                                title="Eliminar"
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal"
                                data-delete-url="{{ route('admin.sesiones.destroy', $sesion) }}"
                                data-item-name="la sesión de {{ $sesion->tipoNombre }}">
                                <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Editar Sesión --}}
<div class="modal fade" id="editSesionModal" tabindex="-1" aria-labelledby="editSesionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSesionModalLabel">
                    <i class="fas fa-edit me-2"></i> Editar Sesión: <span id="editSesionNombre"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="editSesionForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium mb-1" style="font-size: 0.875rem;">Fecha de la sesión</label>
                            <input type="date" name="fecha_sesion" id="editFechaSesion" class="input-modern" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium mb-1" style="font-size: 0.875rem;">Hora de inicio</label>
                            <input type="time" name="horario" id="editHora" class="input-modern" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium mb-1" style="font-size: 0.875rem;">Duración</label>
                            <input type="text" name="duracion" id="editDuracion" class="input-modern" placeholder="Ej: 15 min, 6 vueltas">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium mb-1" style="font-size: 0.875rem;">Observaciones</label>
                            <textarea name="observaciones" id="editObservaciones" class="input-modern" rows="2" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editSesionModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const btn = event.relatedTarget;
        document.getElementById('editSesionNombre').textContent = btn.dataset.label;
        document.getElementById('editFechaSesion').value = btn.dataset.fechaSesion;
        document.getElementById('editHora').value = btn.dataset.hora;
        document.getElementById('editDuracion').value = btn.dataset.duracion;
        document.getElementById('editObservaciones').value = btn.dataset.observaciones;
        document.getElementById('editSesionForm').action = btn.dataset.horarioUrl;
    });
});
</script>
@endif

@include('components.admin.delete-modal')

@endsection