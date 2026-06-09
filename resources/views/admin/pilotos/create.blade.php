@extends('layouts.form')
@section('title', 'Crear Piloto')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Piloto'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Piloto',
            'action' => route('admin.pilotos.store'),
            'cancelRoute' => route('admin.pilotos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Piloto',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Max Verstappen',
                    'width' => 8
                ],
                [
                    'name' => 'pais',
                    'label' => 'País',
                    'type' => 'text',
                    'placeholder' => 'Ej: Argentina',
                    'width' => 4
                ],
                [
                    'name' => 'numero_auto',
                    'label' => 'N° de Auto',
                    'type' => 'number',
                    'min' => 0,
                    'placeholder' => 'Ej: 1',
                    'required' => false,
                    'width' => 4,
                    'help' => 'Sólo si se asignó un campeonato'
                ]
            ]
        ])
    </div>
</div>
@endsection
