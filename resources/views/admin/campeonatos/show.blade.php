@extends('layouts.admin')

@section('title', 'Ver Campeonato')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información del Campeonato',
        'fields' => [
            [
                'label' => 'Nombre',
                'type' => 'text',
                'value' => $campeonato->nombre,
                'width' => 6,
                'icon' => 'fas fa-trophy'
            ],
            [
                'label' => 'Año',
                'type' => 'number',
                'value' => $campeonato->anio,
                'width' => 6,
                'icon' => 'fas fa-calendar-alt'
            ],
            [
                'label' => 'Total de Fechas',
                'type' => 'badge',
                'value' => $campeonato->fechas->count(),
                'width' => 6,
                'color' => 'primary'
            ],
        ],
        'actions' => [
             [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.campeonatos.edit', $campeonato),
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
                    'bs-target' => '#deleteCircuitoModal',
                    'delete-url' => route('admin.campeonatos.destroy', $campeonato),
                    'item-name' => $campeonato->nombre
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.campeonatos.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])
{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteCircuitoModal',
    'title' => 'Confirmar Eliminación de Campeonato',
    'message' => '¿Estás seguro de que deseas eliminar el campeonato',
    'warningText' => 'Esta acción eliminará permanentemente el campeonato y todas sus fechas asociadas a el.',
    'confirmText' => 'Eliminar Campeonato',
    'cancelText' => 'Cancelar'
])
@endsection