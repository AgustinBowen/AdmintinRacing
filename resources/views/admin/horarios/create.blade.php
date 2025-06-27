@extends('layouts.admin')
@section('title', 'Crear Horario')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Horario'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Horario',
            'action' => route('admin.horarios.store'),
            'cancelRoute' => route('admin.horarios.index'),
            'fields' => [
                [
                    'name' => 'fecha_id',
                    'label' => 'Fecha',
                    'type' => 'select',
                    'options' => $fechas,
                    'optionLabel' => 'nombre',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'sesion_id',
                    'label' => 'Sesion',
                    'type' => 'select',
                    'options' => $sesiones,
                    'optionLabel' => 'tipo',
                    'optionValue' => 'id',
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'horario',
                    'label' => 'Hora de Inicio',
                    'type' => 'time',
                    'required' => true,
                    'width' => 4
                ],
                [
                    'name' => 'duracion',
                    'label' => 'Duración',
                    'type' => 'text',
                    'placeholder' => 'Ejemplo: 1 hora',
                    'required' => true
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