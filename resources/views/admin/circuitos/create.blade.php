@extends('layouts.admin')
@section('title', 'Crear Circuito')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nuevo Circuito'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Circuito',
            'action' => route('admin.circuitos.store'),
            'cancelRoute' => route('admin.circuitos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Circuito',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Circuito de Mónaco',
                    'width' => 8
                ],
                [
                    'name' => 'distancia',
                    'label' => 'Distancia (km)',
                    'type' => 'number',
                    'placeholder' => 'Ej: 3.337',
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection