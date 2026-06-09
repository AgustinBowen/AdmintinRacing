@extends('layouts.form')
@section('title', 'Editar Categoría')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Editar Categoría: ' . $categoria->nombre
])

<div class="row">
    <div class="col-md-8">
        @include('components.admin.form', [
            'title' => 'Información de la Categoría',
            'action' => route('admin.categorias.update', $categoria),
            'method' => 'PUT',
            'cancelRoute' => route('admin.categorias.index'),
            'fields' => [
                [
                    'name' => 'nombre',
                    'label' => 'Nombre de la Categoría',
                    'type' => 'text',
                    'required' => true,
                    'value' => $categoria->nombre,
                    'width' => 12
                ],
                [
                    'name' => 'descripcion',
                    'label' => 'Descripción',
                    'type' => 'textarea',
                    'required' => false,
                    'value' => $categoria->descripcion,
                    'width' => 12
                ],
                [
                    'name' => 'activa',
                    'label' => 'Activa',
                    'type' => 'checkbox',
                    'required' => false,
                    'value' => $categoria->activa,
                    'width' => 12
                ]
            ]
        ])
    </div>
</div>
@endsection
