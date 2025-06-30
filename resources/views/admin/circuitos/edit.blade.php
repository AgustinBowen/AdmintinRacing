@extends('layouts.admin')

@section('title', 'Editar Circuito')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Circuito'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Circuito',
            'action' => route('admin.circuitos.update', $circuito->id),
            'method' => 'PUT',
            'cancelRoute' => route('admin.circuitos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Circuito',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Circuito de Mónaco',
                    'value' => $circuito->nombre,
                    'width' => 8
                ],
                [
                    'name' => 'distancia',
                    'label' => 'Distancia (km)',
                    'type' => 'number',
                    'placeholder' => 'Ej: 3.337',
                    'value' => $circuito->distancia,
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection