@extends('layouts.admin')

@section('title', 'Editar Fecha')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Fecha'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información de la Fecha',
            'action' => route('admin.fechas.update', $fecha->id),
            'method' => 'PUT',
            'cancelRoute' => route('admin.fechas.index'),
            'model' => $fecha,
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre de la Fecha',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Fecha 1 2025',
                    'value' => $fecha->nombre,
                    'width' => 8
                ],
                [
                    'name' => 'fecha_desde',
                    'label' => 'Fecha Desde',
                    'type' => 'date',
                    'placeholder' => 'Ej: 2025-01-01',
                    'value' => $fecha->fecha_desde->format('Y-m-d'),
                    'width' => 4
                ],
                [
                    'name' => 'fecha_hasta',
                    'label' => 'Fecha Hasta',
                    'type' => 'date',
                    'placeholder' => 'Ej: 2025-12-07',
                    'value' => $fecha->fecha_hasta->format('Y-m-d'),
                    'width' => 4
                ],
                [
                    'name' => 'circuito_id',
                    'label' => 'Circuito',
                    'type' => 'select',
                    'options' => $circuitos,
                    'optionLabel' => 'nombre',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8,
                    'value' => $fecha->circuito_id
                ],
                [
                    'name' => 'campeonato_id',
                    'label' => 'Campeonato',
                    'type' => 'select',
                    'options' => $campeonatos,
                    'optionLabel' => 'campeonato',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8,
                    'value' => $fecha->campeonato_id
                ]
            ]
        ])
    </div>
</div>
@endsection