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
    'createText' => 'Nuevo Sesión',
    'emptyMessage' => 'No hay sesiones registradas',
    'showView'=> false,
    'showEdit' => true,
    'columns' => [
        ['label' => 'ID', 'field' => 'id'],
        ['label' => 'Tipo de Sesión', 'field' => 'tipo'],
        ['label' => 'Fecha de Inicio', 'field' => 'fecha_inicio'],
        ['label' => 'Fecha de Fin', 'field' => 'fecha_fin'],
        ['label' => 'Estado', 'field' => 'estado'],
        ['label' => 'Acciones', 'field' => 'actions']
    ]
])
@endsection