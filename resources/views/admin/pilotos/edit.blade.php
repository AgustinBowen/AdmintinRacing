@extends('layouts.admin')

@section('title', 'Editar Piloto')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Piloto'
])

<div class="row">
    <div class="col-md-10">
        @include('components.admin.form', [
            'title' => 'Información del Piloto',
            'action' => route('admin.pilotos.update', $piloto->id),
            'method' => 'PUT',
            'cancelRoute' => route('admin.pilotos.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre del Piloto',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Max Verstappen',
                    'value' => $piloto->nombre,
                    'width' => 8
                ],
                [
                    'name' => 'pais',
                    'label' => 'Pais',
                    'type' => 'text',
                    'placeholder' => 'Ej: Argentina',
                    'value' => $piloto->pais,
                    'width' => 4
                ]
            ]
        ])
    </div>
</div>
@endsection