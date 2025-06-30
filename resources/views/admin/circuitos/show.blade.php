@extends('layouts.admin')

@section('title', 'Ver Circuito')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información del Circuito',
        'fields' => [
            [
                'label' => 'Nombre',
                'type' => 'text',
                'value' => $circuito->nombre,
                'width' => 8,
                'icon' => 'fas fa-flag-checkered'
            ],
            [
                'label' => 'Distancia',
                'type' => 'text',
                'value' => number_format($circuito->distancia, 3) . ' km',
                'width' => 4,
                'icon' => 'fas fa-route'
            ],
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.circuitos.edit', $circuito),
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
                    'delete-url' => route('admin.circuitos.destroy', $circuito),
                    'item-name' => $circuito->nombre
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.circuitos.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteCircuitoModal',
    'title' => 'Confirmar Eliminación de Circuito',
    'message' => '¿Estás seguro de que deseas eliminar el circuito',
    'warningText' => 'Esta acción eliminará permanentemente el circuito y no se puede deshacer.',
    'confirmText' => 'Eliminar Circuito',
    'cancelText' => 'Cancelar'
])

@endsection