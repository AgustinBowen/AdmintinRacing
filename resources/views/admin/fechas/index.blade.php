@extends('layouts.admin')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Calendario',
    'actions' => '
        <a href="'.route('admin.fechas.create').'" class="btn" style="background:var(--white); color:var(--black);">
            + NUEVA FECHA
        </a>
    '
])

<div class="calendar-list">
    @forelse($fechas as $fecha)
        @php
            $startMonth = \Carbon\Carbon::parse($fecha->fecha_desde)->translatedFormat('M');
            $endMonth = \Carbon\Carbon::parse($fecha->fecha_hasta)->translatedFormat('M');
            
            $startDay = \Carbon\Carbon::parse($fecha->fecha_desde)->format('d');
            $endDay = \Carbon\Carbon::parse($fecha->fecha_hasta)->format('d');
            
            if ($startMonth == $endMonth) {
                $monthStr = $startMonth;
                $daysStr = $startDay . ' - ' . $endDay;
            } else {
                $monthStr = $startMonth . '/' . $endMonth;
                $daysStr = $startDay . ' - ' . $endDay;
            }
        @endphp
        <div class="calendar-card">
            <div class="calendar-date">
                <span class="month">{{ $monthStr }}</span>
                <span class="days">{{ $daysStr }}</span>
            </div>
            
            <div class="calendar-info">
                <h3 class="title" title="{{ $fecha->nombre }}">{{ $fecha->nombre }}</h3>
                <div class="circuit" title="{{ $fecha->circuito->nombre ?? 'Sin Circuito Asignado' }}">
                    <x-heroicon-o-flag style="width:1em; height:1em; vertical-align:-0.125em; color: var(--gray-dim);" />
                    {{ $fecha->circuito->nombre ?? 'Sin Circuito Asignado' }}
                </div>
            </div>
            
            <div class="calendar-actions">
                <a href="{{ route('admin.fechas.resultados', $fecha) }}" class="btn ghost sm" title="Ver Resultados">
                    <x-heroicon-o-list-bullet style="width:1em; height:1em; vertical-align:-0.125em;" /> <span class="action-text">Resultados</span>
                </a>
                <a href="{{ route('admin.fechas.show', $fecha) }}" class="btn ghost sm" title="Ver Cronograma">
                    <x-heroicon-o-eye style="width:1em; height:1em; vertical-align:-0.125em;" />
                </a>
                <a href="{{ route('admin.fechas.edit', $fecha) }}" class="btn ghost sm" title="Editar">
                    <x-heroicon-o-pencil style="width:1em; height:1em; vertical-align:-0.125em;" />
                </a>
                <button type="button" class="btn danger sm" title="Eliminar" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-url="{{ route('admin.fechas.destroy', $fecha) }}" data-item-name="{{ $fecha->nombre }}" style="display: inline-flex; align-items: center; justify-content: center;">
                    <x-heroicon-o-trash style="width:1em; height:1em; vertical-align:-0.125em;" />
                </button>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; background:var(--carbon); border:1px solid var(--line); color:var(--gray);">
            No hay fechas registradas en este campeonato.
        </div>
    @endforelse
</div>

@if(method_exists($fechas, 'links'))
<div style="margin-top:20px;">
    {{ $fechas->links() }}
</div>
@endif

@push('styles')
<style>
@media (max-width: 768px) {
    .calendar-actions .action-text { display: none; }
}
</style>
@endpush

@include('components.admin.delete-modal', [
    'warningText' => 'Se eliminarán de forma permanente todas sus sesiones, horarios y resultados vinculados.'
])

@endsection