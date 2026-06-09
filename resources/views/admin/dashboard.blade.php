@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Panel de Control',
    'subtitle' => 'Bienvenido a AdmintínRacing'
])

<div style="background:var(--carbon-2); border:1px solid var(--line); border-left:3px solid var(--racing); padding:28px; margin-bottom:24px;">
    <h2 style="font-family:var(--font-display); font-weight:700; font-size:32px; color:var(--white); text-transform:uppercase; margin-bottom:8px;">Gestión de Carreras</h2>
    <p style="font-family:var(--font-sans); font-size:14px; color:var(--gray); max-width:600px;">Administrá fechas, sesiones, pilotos y resultados de manera centralizada. Utilizá el menú lateral para acceder a todas las funcionalidades del sistema.</p>
</div>

<div class="stat-grid">
    <a href="{{ route('admin.campeonatos.show', session('campeonato_id')) }}" style="text-decoration:none; display:block;">
        @include('components.admin.stats-card', [
            'label' => 'Campeonatos',
            'value' => $stats['campeonatos'] ?? 0,
            'small' => 'registrados'
        ])
    </a>
    
    <a href="{{ route('admin.pilotos.index') }}" style="text-decoration:none; display:block;">
        @include('components.admin.stats-card', [
            'label' => 'Pilotos',
            'value' => $stats['pilotos'] ?? 0,
            'small' => 'inscriptos'
        ])
    </a>
    
    <a href="{{ route('admin.circuitos.index') }}" style="text-decoration:none; display:block;">
        @include('components.admin.stats-card', [
            'label' => 'Circuitos',
            'value' => $stats['circuitos'] ?? 0,
            'small' => 'disponibles'
        ])
    </a>
    
    <a href="{{ route('admin.fechas.index') }}" style="text-decoration:none; display:block;">
        @include('components.admin.stats-card', [
            'label' => 'Fechas',
            'value' => $stats['fechas'] ?? 0,
            'small' => 'programadas'
        ])
    </a>
</div>
@endsection