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
                'icon' => 'trophy'
            ],
            [
                'label' => 'Año',
                'type' => 'number',
                'value' => $campeonato->anio,
                'width' => 6,
                'icon' => 'calendar-days'
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
                'label' => 'Ver Clasificación',
                'route' => route('admin.campeonatos.standings', $campeonato),
                'class' => 'btn-primary-modern',
                'icon' => 'trophy'
            ],
            [
                'type' => 'link',
                'label' => 'Sistema de Puntaje',
                'route' => route('admin.campeonatos.scoring', $campeonato),
                'class' => 'btn-secondary-modern',
                'icon' => 'adjustments-horizontal'
            ],
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.campeonatos.edit', $campeonato),
                'class' => 'btn-secondary-modern',
                'icon' => 'pencil-square'
            ],
            [
                'type' => 'button',
                'label' => 'Eliminar',
                'icon' => 'trash',
                'class' => 'btn-destructive-modern',
                'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#deleteCircuitoModal',
                    'delete-url' => route('admin.campeonatos.destroy', $campeonato),
                    'item-name' => $campeonato->nombre
                ]
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