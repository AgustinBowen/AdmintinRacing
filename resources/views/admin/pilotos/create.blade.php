@extends('layouts.admin')
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
                    'label' => 'Pais',
                    'type' => 'text',
                    'placeholder' => 'Ej: Argentina',
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection