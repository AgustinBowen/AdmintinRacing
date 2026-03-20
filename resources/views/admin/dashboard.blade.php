@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Panel de Control',
    'subtitle' => 'Bienvenido a AdmintínRacing'
])

{{-- Hero Carousel --}}
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    {{-- Indicators --}}
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>

    <div class="carousel-inner rounded-3 overflow-hidden" style="height: 520px; box-shadow: var(--shadow-lg);">
        <div class="carousel-item active">
            <img src="{{ asset('images/carousel_1.jpg') }}" class="d-block w-100" alt="Carrera en pista"
                 style="height: 520px; object-fit: cover; object-position: center;">
            <div class="carousel-caption d-none d-md-block text-start ps-5 pb-5">
                <div class="d-inline-block px-3 py-1 rounded-1 mb-2 fw-semibold" style="background: hsl(var(--accent)); color: hsl(var(--accent-foreground)); font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase;">En pista</div>
                <h2 class="fw-bold display-6 text-white mb-2" style="text-shadow: 0 2px 12px rgba(0,0,0,0.7);">Gestión de Carreras</h2>
                <p class="text-white-50 mb-0" style="font-size: 1.05rem;">Administrá fechas, sesiones y resultados de manera eficiente.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="{{ asset('images/carousel_2.jpg') }}" class="d-block w-100" alt="Pit lane"
                 style="height: 520px; object-fit: cover; object-position: center;">
            <div class="carousel-caption d-none d-md-block text-start ps-5 pb-5">
                <div class="d-inline-block px-3 py-1 rounded-1 mb-2 fw-semibold" style="background: hsl(var(--accent)); color: hsl(var(--accent-foreground)); font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase;">Equipo</div>
                <h2 class="fw-bold display-6 text-white mb-2" style="text-shadow: 0 2px 12px rgba(0,0,0,0.7);">Control de Pilotos</h2>
                <p class="text-white-50 mb-0" style="font-size: 1.05rem;">Registrá pilotos y hacé seguimiento de su rendimiento.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="{{ asset('images/carousel_3.jpg') }}" class="d-block w-100" alt="Podio"
                 style="height: 520px; object-fit: cover; object-position: center;">
            <div class="carousel-caption d-none d-md-block text-start ps-5 pb-5">
                <div class="d-inline-block px-3 py-1 rounded-1 mb-2 fw-semibold" style="background: hsl(var(--accent)); color: hsl(var(--accent-foreground)); font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase;">Resultados</div>
                <h2 class="fw-bold display-6 text-white mb-2" style="text-shadow: 0 2px 12px rgba(0,0,0,0.7);">Campeonatos y Podios</h2>
                <p class="text-white-50 mb-0" style="font-size: 1.05rem;">Consultá clasificaciones y resultados de cada evento.</p>
            </div>
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>

{{-- Quick Access Grid --}}
<div class="row g-3 mt-4">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.campeonatos.index') }}" class="card-modern d-flex align-items-center gap-3 p-3 text-decoration-none dashboard-quick-card">
            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:hsl(var(--accent)/0.12);">
                <i class="fas fa-trophy" style="color:hsl(var(--accent));font-size:1.2rem;"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:0.9rem;color:hsl(var(--foreground));">Campeonatos</div>
                <div style="font-size:0.78rem;color:hsl(var(--muted-foreground));">{{ $stats['campeonatos'] ?? 0 }} registrados</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.pilotos.index') }}" class="card-modern d-flex align-items-center gap-3 p-3 text-decoration-none dashboard-quick-card">
            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:hsl(var(--accent)/0.12);">
                <i class="fas fa-helmet-safety" style="color:hsl(var(--accent));font-size:1.2rem;"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:0.9rem;color:hsl(var(--foreground));">Pilotos</div>
                <div style="font-size:0.78rem;color:hsl(var(--muted-foreground));">{{ $stats['pilotos'] ?? 0 }} registrados</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.circuitos.index') }}" class="card-modern d-flex align-items-center gap-3 p-3 text-decoration-none dashboard-quick-card">
            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:hsl(var(--accent)/0.12);">
                <i class="fas fa-road" style="color:hsl(var(--accent));font-size:1.2rem;"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:0.9rem;color:hsl(var(--foreground));">Circuitos</div>
                <div style="font-size:0.78rem;color:hsl(var(--muted-foreground));">{{ $stats['circuitos'] ?? 0 }} disponibles</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.fechas.index') }}" class="card-modern d-flex align-items-center gap-3 p-3 text-decoration-none dashboard-quick-card">
            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:hsl(var(--accent)/0.12);">
                <i class="fas fa-calendar-days" style="color:hsl(var(--accent));font-size:1.2rem;"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:0.9rem;color:hsl(var(--foreground));">Fechas</div>
                <div style="font-size:0.78rem;color:hsl(var(--muted-foreground));">{{ $stats['fechas'] ?? 0 }} programadas</div>
            </div>
        </a>
    </div>
</div>

<style>
.dashboard-quick-card {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.dashboard-quick-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg) !important;
}
</style>
@endsection