@extends('layouts.admin')
@section('title', 'Crear Sesion')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nueva Sesión'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información de la Sesion',
            'action' => route('admin.sesiones.store'),
            'cancelRoute' => route('admin.sesiones.index'),
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
                    'name' => 'tipo',
                    'label' => 'Tipo de Sesión',
                    'type' => 'select',
                    'options' => $tipos,
                    'optionLabel' => 'label',
                    'optionValue' => 'value',
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'fecha_sesion',
                    'label' => 'Fecha de la Sesión',
                    'type' => 'date',
                    'required' => true,
                    'width' => 8
                ],
            ]
        ])
    </div>
</div>
@endsection