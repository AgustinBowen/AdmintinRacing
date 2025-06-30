@extends('layouts.admin')

@section('title', 'Editar Sesión')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Sesión'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información de la Sesión',
            'action' => route('admin.sesiones.update', $sesion),
            'method' => 'PUT',
            'cancelRoute' => route('admin.sesiones.show', $sesion),
            'fields' => [
                [
                    'name' => 'fecha_id',
                    'label' => 'Fecha',
                    'type' => 'select',
                    'options' => $fechas,
                    'optionLabel' => 'nombre',
                    'optionValue' => 'id',
                    'value' => $sesion->fecha_id,
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
                    'value' => $sesion->tipo,
                    'required' => true,
                    'width' => 8
                ],
                [
                    'name' => 'fecha_sesion',
                    'label' => 'Fecha de la Sesión',
                    'type' => 'date',
                    'value' => $sesion->fecha_sesion,
                    'required' => true,
                    'width' => 8
                ],
            ]
        ])
    </div>
</div>
@endsection