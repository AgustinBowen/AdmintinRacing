@extends('layouts.admin')
@section('title', 'Crear Campeonato')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Campeonato'
])

<div class="row">
    <div class="col-md-8">
        @include('components.admin.form', [
            'title' => 'Información del Campeonato',
            'action' => route('admin.campeonatos.store'),
            'cancelRoute' => route('admin.campeonatos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Campeonato',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Fórmula 1 2024',
                    'width' => 8
                ],
                [
                    'name' => 'anio',
                    'label' => 'Año',
                    'type' => 'number',
                    'required' => true,
                    'value' => date('Y'),
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection