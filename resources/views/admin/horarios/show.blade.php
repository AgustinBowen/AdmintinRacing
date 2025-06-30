@extends('layouts.admin')

@section('title', 'Ver Horario')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información del Horario',
        'fields' => [
            [
                'label' => 'Fecha del Evento',
                'type' => 'text',
                'value' => $horario->fecha->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-calendar-alt'
            ],
            [
                'label' => 'Tipo de Sesión',
                'type' => 'text',
                'value' => $horario->sesion->tipo ?? '—',
                'width' => 6,
                'icon' => 'fas fa-flag-checkered'
            ],
            [
                'label' => 'Horario de Inicio',
                'type' => 'time',
                'value' => $horario->horario,
                'format' => 'H:i',
                'width' => 4,
                'icon' => 'fas fa-clock'
            ],
            [
                'label' => 'Duración',
                'type' => 'text',
                'value' => $horario->duracion ?? '—',
                'width' => 4,
                'icon' => 'fas fa-hourglass-half'
            ],
            [
                'label' => 'Horario Completo',
                'type' => 'text',
                'value' => $horario->horario && $horario->duracion 
                    ? \Carbon\Carbon::parse($horario->horario)->format('H:i') . ' (' . $horario->duracion . ')'
                    : '—',
                'width' => 4,
                'icon' => 'fas fa-stopwatch'
            ],
            [
                'label' => 'Circuito',
                'type' => 'text',
                'value' => $horario->fecha->circuito->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-road'
            ],
            [
                'label' => 'Observaciones',
                'type' => 'textarea',
                'value' => $horario->observaciones ?? 'Sin observaciones adicionales',
                'width' => 12,
                'icon' => 'fas fa-sticky-note'
            ]
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.horarios.edit', $horario),
                'class' => 'btn-primary-modern',
                'icon' => 'fas fa-edit'
            ],
            [
                'type' => 'button',
                'label' => 'Eliminar',
                'icon' => 'fas fa-trash',
                'class' => 'btn-destructive-modern',
                'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#deleteHorarioModal',
                    'delete-url' => route('admin.horarios.destroy', $horario),
                    'item-name' => ($horario->fecha->nombre ?? 'Horario') . ' - ' . ($horario->sesion->tipo ?? 'Sesión') . ' (' . \Carbon\Carbon::parse($horario->horario)->format('H:i') . ')'
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.horarios.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteHorarioModal',
    'title' => 'Confirmar Eliminación de Horario',
    'message' => '¿Estás seguro de que deseas eliminar el horario',
    'warningText' => 'Esta acción eliminará permanentemente el horario y no se puede deshacer.',
    'confirmText' => 'Eliminar Horario',
    'cancelText' => 'Cancelar'
])

@endsection