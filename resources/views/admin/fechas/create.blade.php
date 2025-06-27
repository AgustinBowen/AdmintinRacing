@extends('layouts.admin')
@section('title', 'Crear Fecha')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Fecha'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información de la Fecha',
            'action' => route('admin.fechas.store'),
            'cancelRoute' => route('admin.fechas.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre de la Fecha',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Fecha 1 2025',
                    'width' => 8
                ],
                [
                    'name' => 'fecha_desde',
                    'label' => 'Fecha Desde',
                    'type' => 'date',
                    'placeholder' => 'Ej: 2025-01-01',
                    'width' => 4
                ],
                [
                    'name' => 'fecha_hasta',
                    'label' => 'Fecha Hasta',
                    'type' => 'date',
                    'placeholder' => 'Ej: 2025-12-07',
                    'width' => 4
                ],
                [
                    'name' => 'circuito',
                    'label' => 'Circuito',
                    'type' => 'select',
                    'options' => $circuitos,
                    'optionLabel' => 'nombre',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'campeonato_id',
                    'label' => 'Campeonato',
                    'type' => 'select',
                    'options' => $campeonatos,
                    'optionLabel' => 'campeonato',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8
                ]
            ]
        ])
    </div>
</div>
@endsection