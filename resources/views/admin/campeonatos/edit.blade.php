@extends('layouts.admin')

@section('title', 'Editar Campeonato')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Campeonato'
])

<div class="row">
    <div class="col-md-8">
        @include('components.admin.form', [
            'title' => 'Información del Campeonato',
            'action' => route('admin.campeonatos.update', $campeonato->id),
            'method' => 'PUT',
            'cancelRoute' => route('admin.campeonatos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Campeonato',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Fórmula 1 2024',
                    'value' => $campeonato->nombre,
                    'width' => 8
                ],
                [
                    'name' => 'anio',
                    'label' => 'Año',
                    'type' => 'number',
                    'required' => true,
                    'value' => $campeonato->anio,
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection