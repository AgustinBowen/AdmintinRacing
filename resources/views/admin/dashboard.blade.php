@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')


<div style="position: relative; overflow: hidden; border: 1px solid var(--line); margin-bottom: 24px; min-height: 380px; display: flex; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
    
    <!-- Video de fondo (ocupa el 100% de la caja) -->
    <video autoplay loop muted playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; opacity: 0.7; filter: saturate(1.2) contrast(1.1);">
        <source src="https://res.cloudinary.com/dmtwaxmgz/video/upload/v1781386777/header_agdtvw.webm" type="video/webm">
    </video>

    <!-- Overlay Gradiente para legibilidad -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(12,12,12,0.95) 0%, rgba(12,12,12,0.75) 45%, rgba(12,12,12,0.1) 100%); z-index: 2;"></div>

    <!-- Contenido principal -->
    <div style="position: relative; z-index: 3; padding: 48px; max-width: 700px;">
        
        <h2 style="font-family: var(--font-display); font-weight: 800; font-size: 48px; color: var(--white); text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.02em; line-height: 1.05;">
            Bienvenido a<br>
            <span style="color: var(--accent);">Admintin<span style="color: var(--gray);">Racing</span></span>
        </h2>
        
        <p style="font-family: var(--font-sans); font-size: 16px; color: var(--bone); line-height: 1.6; text-shadow: 0 2px 8px rgba(0,0,0,0.9); margin-bottom: 32px;">
            Administrá fechas, sesiones, pilotos y resultados de manera centralizada. Utilizá el menú lateral para acceder a todas las funcionalidades del sistema.
        </p>
        
        <div style="display: flex; gap: 16px;">
            <a href="{{ route('admin.fechas.index') }}" class="btn" style="background: var(--accent); color: var(--black); border: none; padding: 0 32px; height: 48px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; letter-spacing: 0.05em; font-size: 13px; transition: 0.3s;">
                Ver Fechas <x-heroicon-o-chevron-right style="width:1em; height:1em; vertical-align:-0.125em; margin-left: 10px; font-size: 11px;" />
            </a>
            <a href="{{ route('admin.pilotos.index') }}" class="btn ghost" style="height: 48px; display: inline-flex; align-items: center; justify-content: center; padding: 0 32px; font-size: 13px; background: var(--black)">
                Ver Pilotos cargados
            </a>
        </div>
    </div>
</div>

@if(isset($top5) && count($top5) > 0)
<div style="background: var(--carbon-2); border: 1px solid var(--line); padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--white); text-transform: uppercase; margin: 0;">
                Top 5 del Campeonato
            </h3>
            <p style="font-family: var(--font-sans); font-size: 13px; color: var(--gray); margin: 4px 0 0 0;">
                Posiciones actuales de la temporada en curso
            </p>
        </div>
        <a href="{{ route('admin.campeonatos.standings', session('campeonato_id')) }}" class="btn ghost sm">
            Ver Completo &rarr;
        </a>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-family: var(--font-sans);">
            <thead>
                <tr style="border-bottom: 1px solid var(--line); color: var(--gray); font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 12px 8px; width: 60px; text-align: center;">Pos</th>
                    <th style="padding: 12px 8px;">Piloto</th>
                    <th style="padding: 12px 8px; width: 100px; text-align: right;">Puntos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top5 as $index => $pos)
                <tr style="border-bottom: 1px solid var(--line); color: var(--bone); transition: 0.2s;">
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600;">
                        @if($index == 0)
                            <x-heroicon-o-trophy style="width:1em; height:1em; vertical-align:-0.125em; color: #ffd700; font-size: 16px;" />
                        @elseif($index == 1)
                            <x-heroicon-o-trophy style="width:1em; height:1em; vertical-align:-0.125em; color: #c0c0c0; font-size: 16px;" />
                        @elseif($index == 2)
                            <x-heroicon-o-trophy style="width:1em; height:1em; vertical-align:-0.125em; color: #cd7f32; font-size: 16px;" />
                        @else
                            {{ $index + 1 }}
                        @endif
                    </td>
                    <td style="padding: 12px 8px; font-weight: 600; font-size: 15px;">{{ $pos->piloto->nombre ?? 'Desconocido' }}</td>
                    <td style="padding: 12px 8px; font-weight: 700; color: var(--accent); text-align: right; font-size: 16px;">{{ $pos->puntos_totales }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection