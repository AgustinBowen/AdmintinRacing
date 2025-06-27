@extends('layouts.admin')
@section('title', 'Horarios')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Gestión de Horarios',
    'subtitle' => 'Administra todos los horarios registrados'
])

@include('components.admin.table', [
    'title' => 'Lista de Horarios',
    'items' => $horarios,
    'routePrefix' => 'admin.horarios',
    'createRoute' => route('admin.horarios.create'),
    'createText' => 'Nuevo Horario',
    'emptyMessage' => 'No hay horarios registrados',
    'showView'=> false,
    'showEdit' => true,
    'columns' => [
        ['field' => 'fecha', 'label' => 'Fecha', 'type' => 'text'],
        ['tipo_sesion']

    ]
])
@endsection