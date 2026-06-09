@extends('layouts.selector')
@section('title', 'Campeonatos')

@section('content')
<section class="screen active" id="years" style="display:flex;">
    <div class="yr-head">
        <div>
            <div class="crumb">
                <span>AdminTin</span><span class="sep">/</span>
                <b>{{ $categoria->nombre }}</b><span class="sep">/</span>
                <span>Temporada</span>
            </div>
            <h2>Elegí la temporada</h2>
        </div>
        <div class="head-right">
            <a href="{{ route('admin.categorias.index') }}" class="yr-back">&larr; Cambiar categoría</a>
            <div class="search-box yr-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="yrSearch" placeholder="Buscar temporada…" autocomplete="off" />
            </div>
            <a href="{{ route('admin.categorias.campeonatos.create', $categoria) }}" class="btn sm" style="margin-top:8px;">+ Nuevo</a>
        </div>
    </div>
    <div class="checker-strip dim"></div>
    <div class="yr-grid" id="yrGrid" style="display:grid;">
        @foreach($categoria->campeonatos as $campeonato)
        <a href="{{ route('admin.categorias.campeonatos.seleccionar', [$categoria, $campeonato]) }}" class="yr-card show" style="text-decoration:none;">
            <span class="yr-badge">Disponible</span>
            <div class="yr-num">{{ $campeonato->anio }}</div>
            <div class="yr-block">
                <div class="yr-k">Campeonato</div>
                <div class="yr-v">{{ $campeonato->nombre }}</div>
            </div>
            <div class="yr-block">
                <div class="yr-k">Fechas</div>
                <div class="yr-v">{{ $campeonato->fechas_count ?? 0 }}</div>
            </div>
            <div class="yr-corner">Seleccionar</div>
        </a>
        @endforeach
    </div>
</section>
@endsection
