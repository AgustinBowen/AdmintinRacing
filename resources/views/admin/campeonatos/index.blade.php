@extends('layouts.admin')
@section('title', 'Campeonatos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Campeonatos',
    'subtitle' => 'Administra todos los campeonatos de la temporada'
])

@include('components.admin.table', [
    'title' => 'Lista de Campeonatos',
    'items' => $campeonatos,
    'routePrefix' => 'admin.campeonatos',
    'createRoute' => route('admin.campeonatos.create'),
    'createText' => 'Nuevo Campeonato',
    'emptyMessage' => 'No hay campeonatos registrados',
    'columns' => [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'anio', 'label' => 'Año', 'type' => 'badge', 'color' => 'info'],
        ['field' => 'fechas_count', 'label' => 'Fechas', 'type' => 'badge', 'color' => 'primary'],
    ]
])
@endsection