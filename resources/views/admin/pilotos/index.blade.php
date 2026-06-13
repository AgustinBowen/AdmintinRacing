@extends('layouts.admin')
@section('title', 'Pilotos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Pilotos',
])

@include('components.admin.table', [
    'title' => 'Lista de Pilotos',
    'items' => $pilotos,
    'routePrefix' => 'admin.pilotos',
    'createRoute' => route('admin.pilotos.create'),
    'createText' => 'Nuevo Piloto',
    'extraButtons' => [
        [
            'text' => 'Importar de PDF',
            'url' => route('admin.pilotos.import.form'),
            'icon' => 'document-text',
            'class' => 'ghost mx-2'
        ]
    ],
    'emptyMessage' => 'No hay pilotos inscriptos en este campeonato',
    'showView' => true,
    'showEdit' => true,
    'showDelete' => true,
    'columns' => $columns ?? [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
    ]
])
@endsection