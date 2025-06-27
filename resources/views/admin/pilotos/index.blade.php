@extends('layouts.admin')
@section('title', 'Pilotos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Pilotos',
    'subtitle' => 'Administra todos los pilotos registrados'
])

@include('components.admin.table', [
    'title' => 'Lista de Pilotos',
    'items' => $pilotos,
    'routePrefix' => 'admin.pilotos',
    'createRoute' => route('admin.pilotos.create'),
    'createText' => 'Nuevo Piloto',
    'emptyMessage' => 'No hay pilotos registrados',
    'showView'=> true,
    'showEdit' => true,
    'columns' => [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
    ]
])
@endsection