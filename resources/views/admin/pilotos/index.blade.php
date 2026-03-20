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
    'createRoute' => route('admin.pilotos.create', ['campeonato_id' => request('campeonato_id')]),
    'createText' => 'Nuevo Piloto',
    'extraButtons' => [
        [
            'text' => 'Importar de PDF',
            'url' => route('admin.pilotos.import.form'),
            'icon' => 'fas fa-file-pdf',
            'class' => 'btn-modern btn-secondary-modern mx-2'
        ]
    ],
    'filters' => [
        [
            'key' => 'campeonato_id',
            'label' => 'Filtrar por Campeonato',
            'type' => 'select'
        ]
    ],
    'filterOptions' => [
        'campeonato_id' => $campeonatosOptions
    ],
    'requireFilter' => $requireFilter ?? false,
    'requireFilterMessage' => 'Seleccioná un campeonato para ver los pilotos inscriptos',
    'requireFilterIcon' => 'fas fa-filter',
    'emptyMessage' => 'No hay pilotos para este campeonato',
    'showView' => true,
    'showEdit' => true,
    'showDelete' => true,
    'columns' => $columns ?? [
        ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
        ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
    ]
])
@endsection