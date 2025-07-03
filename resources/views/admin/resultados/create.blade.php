@extends('layouts.admin')
@section('title', 'Crear Resultado de Sesión')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Resultado de Sesión'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Resultado',
            'action' => route('admin.resultados.store'),
            'cancelRoute' => route('admin.resultados.index'),
            'fields' => [
                [
                    'name' => 'sesion_id',
                    'label' => 'Sesión',
                    'type' => 'searchable_select',
                    'required' => true,
                    'search_url' => route('admin.resultados.search-sesiones'),
                    'placeholder' => 'Buscar sesión...',
                    'width' => 6
                ],
                [
                    'name' => 'piloto_id',
                    'label' => 'Piloto',
                    'type' => 'searchable_select',
                    'required' => true,
                    'search_url' => route('admin.resultados.search-pilotos'),
                    'placeholder' => 'Buscar piloto...',
                    'width' => 6
                ],
                [
                    'name' => 'posicion',
                    'label' => 'Posición',
                    'type' => 'number',
                    'min' => 1,
                    'required' => true,
                    'width' => 3
                ],
                [
                    'name' => 'tiempo_total',
                    'label' => 'Tiempo total carrera',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 16:25.543',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'mejor_tiempo',
                    'label' => 'Mejor Tiempo',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 1:25.543',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'diferencia_primero',
                    'label' => 'Diferencia con el Primero',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 1.234',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_1',
                    'label' => 'Mejor Sector 1',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 20.556',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_2',
                    'label' => 'Mejor Sector 2',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 40.343',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_3',
                    'label' => 'Mejor Sector 3',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 48.234',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'excluido',
                    'label' => 'Excluido',
                    'type' => 'checkbox',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'presente',
                    'label' => 'Presente',
                    'type' => 'checkbox',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'puntos',
                    'label' => 'Puntos',
                    'type' => 'number',
                    'min' => 0,
                    'step' => '0.1',
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'vueltas',
                    'label' => 'Vueltas Completadas',
                    'type' => 'number',
                    'min' => 0,
                    'required' => false,
                    'width' => 4
                ],
                [
                    'name' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'placeholder' => 'Ingrese observaciones adicionales aquí',
                    'rows' => 3,
                    'width' => 12,
                    'required' => false
                ]
            ]
        ])
    </div>
</div>
@endsection