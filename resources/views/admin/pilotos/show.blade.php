@extends('layouts.admin')

@section('title', 'Pilotos')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información del Piloto',
        'fields' => [
            [
                'label' => 'Nombre',
                'type' => 'text',
                'value' => $piloto->nombre,
                'width' => 8,
                'icon' => 'user'
            ],
            [
                'label' => 'País',
                'type' => 'text',
                'value' => $piloto->pais,
                'width' => 4,
                'icon' => 'flag'
            ],
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.pilotos.edit', $piloto),
                'class' => 'btn-primary-modern',
                'icon' => 'pencil-square'
            ],
            [
                'type' => 'button',
                'label' => 'Eliminar',
                'icon' => 'trash',
                'class' => 'btn-destructive-modern',
                'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#deletePilotoModal',
                    'delete-url' => route('admin.pilotos.destroy', $piloto),
                    'item-name' => $piloto->nombre
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.pilotos.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deletePilotoModal',
    'title' => 'Confirmar Eliminación de Piloto',
    'message' => '¿Estás seguro de que deseas eliminar el piloto',
    'warningText' => 'Esta acción eliminará permanentemente el piloto y no se puede deshacer.',
    'confirmText' => 'Eliminar Piloto',
    'cancelText' => 'Cancelar'
])

@endsection