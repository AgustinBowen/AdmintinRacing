@extends('layouts.admin')

@section('title', 'Ver Sesión')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información de la Sesión',
        'fields' => [
            [
                'label' => 'Fecha',
                'type' => 'text',
                'value' => $sesion->fecha->nombre ?? '—',
                'width' => 8,
                'icon' => 'fas fa-calendar-alt'
            ],
            [
                'label' => 'Tipo de Sesión',
                'type' => 'text',
                'value' => $sesion->tipo ?? '—',
                'width' => 4,
                'icon' => 'fas fa-flag'
            ],
            [
                'label' => 'Fecha de la Sesión',
                'type' => 'date',
                'value' => $sesion->fecha_sesion,
                'format' => 'd/m/Y',
                'showRelative' => true,
                'width' => 6,
                'icon' => 'fas fa-calendar-day'
            ],
            [
                'label' => 'Circuito',
                'type' => 'text',
                'value' => $sesion->fecha->circuito->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-road'
            ],
            [
                'label' => 'Campeonato',
                'type' => 'text',
                'value' => $sesion->fecha->campeonato->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-trophy'
            ],
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.sesiones.edit', $sesion),
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
                    'bs-target' => '#deleteSesionModal',
                    'delete-url' => route('admin.sesiones.destroy', $sesion),
                    'item-name' => 'Sesión de ' . ($sesion->fecha->nombre ?? 'N/A') . ' - ' . ($sesion->tipo ?? 'N/A')
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.sesiones.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteSesionModal',
    'title' => 'Confirmar Eliminación de Sesión',
    'message' => '¿Estás seguro de que deseas eliminar la sesión',
    'warningText' => 'Esta acción eliminará permanentemente la sesión y no se puede deshacer.',
    'confirmText' => 'Eliminar Sesión',
    'cancelText' => 'Cancelar'
])

@endsection