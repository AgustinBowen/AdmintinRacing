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
    'showView'=> true,
    'showEdit' => true,
    'columns' => [
        ['label' => 'Fecha','field' => 'fecha.nombre',  'type' => 'text'],
        ['label' => 'Sesión','field' => 'sesion.tipo',  'type' => 'badge'],
        ['label' => 'Horario','field' => 'horario','type' => 'time'],
        ['label' => 'Duracion','field' => 'duracion',  'type' => 'text'],
        ['label' => 'Observaciones','field' => 'observaciones',  'type' => 'text']
    ]
])
@endsection