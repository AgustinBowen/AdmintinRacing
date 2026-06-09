@extends('layouts.form')
@section('title', 'Crear Categoría')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Crear Nueva Categoría'
])

<div class="row">
    <div class="col-md-8">
        @include('components.admin.form', [
            'title' => 'Información de la Categoría',
            'action' => route('admin.categorias.store'),
            'cancelRoute' => route('admin.categorias.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre de la Categoría',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ej: Turismo Pista 1100',
                    'width' => 12
                ],
                [
                    'name' => 'descripcion',
                    'label' => 'Descripción',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Descripción de la categoría',
                    'width' => 12
                ],
                [
                    'name' => 'activa',
                    'label' => 'Activa',
                    'type' => 'checkbox',
                    'required' => false,
                    'value' => true,
                    'width' => 12
                ]
            ]
        ])
    </div>
</div>
@endsection
