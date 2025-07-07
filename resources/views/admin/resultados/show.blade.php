@extends('layouts.admin')

@section('title', 'Ver Resultado de Sesión')

@section('content')
    @include('components.admin.show', [
        'title' => 'Información del Resultado de Sesión',
        'fields' => [
            [
                'label' => 'Sesión',
                'type' => 'text',
                'value' => $resultado->sesion->tipo ?? '—',
                'width' => 6,
                'icon' => 'fas fa-flag-checkered'
            ],
            [
                'label' => 'Piloto',
                'type' => 'text',
                'value' => $resultado->piloto->nombre ?? '—',
                'width' => 6,
                'icon' => 'fas fa-user-circle'
            ],
            [
                'label' => 'Posición',
                'type' => 'text',
                'value' => $resultado->posicion ?? '—',
                'width' => 3,
                'icon' => 'fas fa-trophy'
            ],
            [
                'label' => 'Puntos',
                'type' => 'text',
                'value' => $resultado->puntos ?? '—',
                'width' => 3,
                'icon' => 'fas fa-star'
            ],
            [
                'label' => 'Presente',
                'type' => 'badge',
                'value' => $resultado->presente ? 'Sí' : 'No',
                'badgeClass' => $resultado->presente ? 'bg-success' : 'bg-secondary',
                'width' => 3,
                'icon' => 'fas fa-check-circle'
            ],
            [
                'label' => 'Excluido',
                'type' => 'badge',
                'value' => $resultado->excluido ? 'Sí' : 'No',
                'badgeClass' => $resultado->excluido ? 'bg-danger' : 'bg-success',
                'width' => 3,
                'icon' => 'fas fa-times-circle'
            ],
            [
                'label' => 'Tiempo Total',
                'type' => 'text',
                'value' => $resultado->tiempo_total_formateado ?? '—',
                'width' => 4,
                'icon' => 'fas fa-clock'
            ],
            [
                'label' => 'Mejor Tiempo',
                'type' => 'text',
                'value' => $resultado->mejor_tiempo_formateado ?? '—',
                'width' => 4,
                'icon' => 'fas fa-stopwatch'
            ],
            [
                'label' => 'Diferencia con el Primero',
                'type' => 'text',
                'value' => $resultado->diferencia_primero_formateada ?? '—',
                'width' => 4,
                'icon' => 'fas fa-chart-line'
            ],
            [
                'label' => 'Vueltas Completadas',
                'type' => 'text',
                'value' => $resultado->vueltas ?? '—',
                'width' => 4,
                'icon' => 'fas fa-redo-alt'
            ],
            [
                'label' => 'Mejor Sector 1',
                'type' => 'text',
                'value' => $resultado->sector_1_formateado ?? '—',
                'width' => 4,
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'label' => 'Mejor Sector 2',
                'type' => 'text',
                'value' => $resultado->sector_2_formateado ?? '—',
                'width' => 4,
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'label' => 'Mejor Sector 3',
                'type' => 'text',
                'value' => $resultado->sector_3_formateado ?? '—',
                'width' => 12,
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'label' => 'Observaciones',
                'type' => 'textarea',
                'value' => $resultado->observaciones ?? '—',
                'width' => 12,
                'icon' => 'fas fa-comment-alt'
            ]
        ],
        'actions' => [
            [
                'type' => 'link',
                'label' => 'Editar',
                'route' => route('admin.resultados.edit', $resultado),
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
                    'bs-target' => '#deleteResultadoModal',
                    'delete-url' => route('admin.resultados.destroy', $resultado),
                    'item-name' => 'Resultado de ' . ($resultado->piloto->nombre_completo ?? 'N/A') . ' - Posición ' . ($resultado->posicion ?? 'N/A')
                ]
            ],
            [
                'type' => 'link',
                'label' => 'Volver a la lista',
                'route' => route('admin.resultados.index'),
                'class' => 'btn-secondary-modern',
                'icon' => 'fas fa-arrow-left'
            ]
        ]
    ])

{{-- Modal de Confirmación de Eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => 'deleteResultadoModal',
    'title' => 'Confirmar Eliminación de Resultado',
    'message' => '¿Estás seguro de que deseas eliminar el resultado',
    'warningText' => 'Esta acción eliminará permanentemente el resultado y no se puede deshacer.',
    'confirmText' => 'Eliminar Resultado',
    'cancelText' => 'Cancelar'
])

@endsection