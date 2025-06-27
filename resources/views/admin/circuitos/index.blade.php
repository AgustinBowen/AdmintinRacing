@extends('layouts.admin')
@section('title', 'Circuitos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Circuitos',
    'subtitle' => 'Administra todos los circuitos disponibles'
])

@include('components.admin.table', [
    'title' => 'Lista de Circuitos',
    'items' => $circuitos,
    'routePrefix' => 'admin.circuitos',
    'createRoute' => route('admin.circuitos.create'),
    'createText' => 'Nuevo Circuito',
    'emptyMessage' => 'No hay circuitos registrados',
    'columns' => [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'distancia', 'label' => 'Distancia (km)', 'type' => 'text'],
    ]
])
@endsection

