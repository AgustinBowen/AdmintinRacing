@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Panel de Control',
    'subtitle' => 'Resumen general del sistema'
])

<div class="row mb-4">
    @include('components.admin.stats-card', [
        'title' => 'Campeonatos',
        'value' => $stats['campeonatos'] ?? 0,
        'label' => 'Total de Campeonatos',
        'icon' => 'trophy',
        'color' => 'primary',
        'width' => 3
    ])
    
    @include('components.admin.stats-card', [
        'title' => 'Pilotos',
        'value' => $stats['pilotos'] ?? 0,
        'label' => 'Pilotos Registrados',
        'icon' => 'user-circle',
        'color' => 'success',
        'width' => 3
    ])
    
    @include('components.admin.stats-card', [
        'title' => 'Circuitos',
        'value' => $stats['circuitos'] ?? 0,
        'label' => 'Circuitos Disponibles',
        'icon' => 'map-marked-alt',
        'color' => 'info',
        'width' => 3
    ])
    
    @include('components.admin.stats-card', [
        'title' => 'Carreras',
        'value' => $stats['fechas'] ?? 0,
        'label' => 'Fechas Programadas',
        'icon' => 'calendar-check',
        'color' => 'warning',
        'width' => 3
    ])
</div>
@endsection