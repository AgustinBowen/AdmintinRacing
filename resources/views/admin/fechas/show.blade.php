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
                'label' => 'Volver a la lista',
                'route' => route('admin.fechas.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteFechaModal',
    'title' => 'Confirmar Eliminación de Fecha',
    'message' => '¿Estás seguro de que deseas eliminar la fecha',
    'warningText' => 'Esta acción eliminará permanentemente la fecha y no se puede deshacer.',
    'confirmText' => 'Eliminar Fecha',
    'cancelText' => 'Cancelar'
])

@endsection