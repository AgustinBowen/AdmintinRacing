@extends('layouts.admin')
@section('title', 'Fechas')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Fechas',
    'subtitle' => 'Administra todas las fechas registrados'
])

@include('components.admin.table', [
    'title' => 'Lista de Fechas',
    'items' => $fechas,
    'routePrefix' => 'admin.fechas',
    'createRoute' => route('admin.fechas.create'),
    'createText' => 'Nueva Fecha',
    'emptyMessage' => 'No hay fechas registrados',
    'showView'=> false,
    'showEdit' => true,
    'showView' => true,
    'rowActions' => [
        [
            'route' => 'admin.fechas.resultados',
            'title' => 'Ver Resultados',
            'icon'  => 'fas fa-list-ol',
            'class' => 'btn-modern btn-primary-modern'
        ]
    ],
    'columns' => [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'fecha_desde', 'label' => 'Fecha Desde', 'type' => 'date'],
        ['field' => 'fecha_hasta', 'label' => 'Fecha Hasta', 'type' => 'date'],
        ['field' => 'circuito.nombre', 'label' => 'Circuito', 'type' => 'text'],
        ['field' => 'campeonato.nombre', 'label' => 'Campeonato', 'type' => 'text'],
    ]
])
@endsection