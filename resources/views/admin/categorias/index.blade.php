@extends('layouts.selector')
@section('title', 'Categorías')

@section('content')
<section class="screen active" id="categories" style="display:flex;">
    <div class="cat-head">
        <div>
            <div class="kicker">AdminTin</div>
            <h2>Elegí la categoría</h2>
        </div>
        <div class="head-right">
            <div class="step">Paso 01 / 02</div>
            <div class="search-box cat-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="catSearch" placeholder="Buscar categoría…" autocomplete="off" />
            </div>
            <a href="{{ route('admin.categorias.create') }}" class="btn sm">+ Nueva</a>
        </div>
    </div>
    <div class="checker-strip dim"></div>
    <div class="cat-list" id="catList">
        @foreach($categorias as $index => $categoria)
        <a href="{{ route('admin.categorias.show', $categoria->id) }}" class="band show" style="text-decoration:none; animation-delay: {{ $index * 0.06 }}s;">
            <div class="speed"></div>
            <div class="ghost">{{ strtoupper(substr($categoria->nombre, 0, 2)) }}</div>
            <div class="inner">
                <span class="idx">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="name">{{ $categoria->nombre }}</span>
                <span class="meta">{{ $categoria->descripcion ?? 'Categoría de carrera' }} &#9656;</span>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endsection
