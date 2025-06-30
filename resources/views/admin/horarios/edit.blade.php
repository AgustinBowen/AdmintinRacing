@extends('layouts.admin')
@section('title', 'Editar Horario')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Horario'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Horario',
            'action' => route('admin.horarios.update', $horario),
            'method' => 'PUT',
            'cancelRoute' => route('admin.horarios.index', $horario),
            'fields' => [
                [
                    'name' => 'fecha_id',
                    'label' => 'Fecha',
                    'type' => 'select',
                    'options' => $fechas,
                    'optionLabel' => 'nombre',
                    'optionValue' => 'id',
                    'value' => $horario->fecha_id,
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
                    'value' => $horario->sesion_id,
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'horario',
                    'label' => 'Hora de Inicio',
                    'type' => 'time',
                    'value' => $horario->horario ? \Carbon\Carbon::parse($horario->horario)->format('H:i') : '',
                    'required' => true,
                    'width' => 4
                ],
                [
                    'name' => 'duracion',
                    'label' => 'Duración',
                    'type' => 'text',
                    'value' => $horario->duracion,
                    'placeholder' => 'Ejemplo: 1 hora',
                    'required' => true
                ],
                [
                    'name' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'value' => $horario->observaciones,
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