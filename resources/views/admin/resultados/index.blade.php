@extends('layouts.admin')
@section('title', 'Resultados')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Resultados',
    'subtitle' => 'Administra todos los resultados de sesiones registrados'
])

@include('components.admin.table', [
    'title' => 'Lista de Resultados',
    'items' => $resultados,
    'routePrefix' => 'admin.resultados',
    'createRoute' => route('admin.resultados.create'),
    'createText' => 'Nuevo Resultado',
    'emptyMessage' => 'No hay resultados registrados',
    'showView'=> true,
    'showEdit' => true,
    'filters' => $filters,
    'filterOptions' => $filterOptions,
    'columns' => [
        ['label' => 'Sesión','field' => 'sesion.tipo',  'type' => 'badge'],
        ['label' => 'Piloto','field' => 'piloto.nombre',  'type' => 'text'],
        ['label' => 'Posición','field' => 'posicion', 'type' => 'text'],
        ['label' => 'Fecha','field' => 'sesion.fecha.nombre', 'type' => 'badge']
    ]
])
@endsection