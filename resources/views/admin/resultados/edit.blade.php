@extends('layouts.form')
@section('title', 'Editar Resultado de Sesión')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Resultado de Sesión'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Resultado',
            'action' => route('admin.resultados.update', $resultado),
            'method' => 'PUT',
            'cancelRoute' => $cancelRoute ?? route('admin.resultados.show', $resultado),
            'fields' => [
                [
                    'name' => 'sesion_id',
                    'label' => 'Sesión',
                    'type' => 'searchable_select',
                    'search_url' => route('admin.resultados.search-sesiones'),
                    'placeholder' => 'Buscar sesión...',
                    'value' => $resultado->sesion_id,
                    'selected_text' => $resultado->sesion->tipo_nombre . ' - ' . ($resultado->sesion->fecha->nombre ?? 'Sin fecha'),
                    'required' => true,
                    'width' => 6
                ],
                [
                    'name' => 'piloto_id',
                    'label' => 'Piloto',
                    'type' => 'searchable_select',
                    'search_url' => route('admin.resultados.search-pilotos'),
                    'placeholder' => 'Buscar piloto...',
                    'value' => $resultado->piloto_id,
                    'selected_text' => $resultado->piloto->nombre,
                    'required' => true,
                    'width' => 6
                ],
                [
                    'name' => 'posicion',
                    'label' => 'Posición',
                    'type' => 'number',
                    'min' => 1,
                    'value' => $resultado->posicion,
                    'required' => true,
                    'width' => 3
                ],
                [
                    'name' => 'tiempo_total',
                    'label' => 'Tiempo total carrera',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 16:25.543',
                    'value' => str_replace('—', '', $resultado->tiempo_total_formateado),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'mejor_tiempo',
                    'label' => 'Mejor Tiempo',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 1:25.543',
                    'value' => str_replace('—', '', $resultado->mejor_tiempo_formateado),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'diferencia_primero',
                    'label' => 'Diferencia con el Primero',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 1.234',
                    'value' => str_replace('—', '', $resultado->diferencia_primero_formateada),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_1',
                    'label' => 'Mejor Sector 1',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 20.556',
                    'value' => str_replace('—', '', $resultado->sector_1_formateado),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_2',
                    'label' => 'Mejor Sector 2',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 40.343',
                    'value' => str_replace('—', '', $resultado->sector_2_formateado),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'sector_3',
                    'label' => 'Mejor Sector 3',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 48.234',
                    'value' => str_replace('—', '', $resultado->sector_3_formateado),
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'excluido',
                    'label' => 'Excluido',
                    'type' => 'checkbox',
                    'value' => $resultado->excluido,
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'presente',
                    'label' => 'Presente',
                    'type' => 'checkbox',
                    'value' => $resultado->presente,
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'puntos',
                    'label' => 'Puntos',
                    'type' => 'number',
                    'min' => 0,
                    'step' => '0.1',
                    'value' => $resultado->puntos,
                    'required' => false,
                    'width' => 3
                ],
                [
                    'name' => 'vueltas',
                    'label' => 'Vueltas Completadas',
                    'type' => 'number',
                    'min' => 0,
                    'value' => $resultado->vueltas,
                    'required' => false,
                    'width' => 4
                ],
                [
                    'name' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'placeholder' => 'Ingrese observaciones adicionales aquí',
                    'value' => $resultado->observaciones,
                    'rows' => 3,
                    'width' => 12,
                    'required' => false
                ]
            ]
        ])
    </div>
</div>
@endsection
