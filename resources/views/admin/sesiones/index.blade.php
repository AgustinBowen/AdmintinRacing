@extends('layouts.admin')
@section('title', 'Sesiones')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Sesiones',
    'subtitle' => 'Administra todas las sesiones del campeonato'
])

@include('components.admin.table', [
    'title' => 'Lista de Sesiones',
    'items' => $sesiones,
    'routePrefix' => 'admin.sesiones',
    'createRoute' => route('admin.sesiones.create'),
    'createText' => 'Nueva Sesión',
    'emptyMessage' => 'No hay sesiones registradas',
    'filters' => $filters,
    'filterOptions' => $filterOptions,
    'showView'=> true,
    'showEdit' => true,
    'showDelete' => true,
    'columns' => [
        ['label' => 'Tipo de Sesión', 'field' => 'tipo','type' => 'badge'],
        ['label' => 'Fecha de la sesión', 'field' => 'fecha_sesion','type' => 'date'],
        ['label' => 'Fecha correspondiente', 'field' => 'fecha.nombre','type' => 'text'],
    ]
])
@endsection