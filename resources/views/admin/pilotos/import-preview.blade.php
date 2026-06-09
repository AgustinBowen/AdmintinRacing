@extends('layouts.selector')
@section('title', 'Revisión de Pilotos')

@section('content')
<section class="screen active" id="pdfReview" style="display:flex;">
    <div class="form-top">
        <a href="{{ route('admin.pilotos.import.form') }}" class="back-link">&larr; Volver al importador</a>
        <div class="tag">Paso 2 de 2</div>
    </div>
    
    <div class="review-body">
        <div class="review-head">
            <h1>Datos Extraídos</h1>
            <div class="sub">Revisá y corregí los nombres antes de guardar definitivamente.</div>
        </div>
        
        <div class="notice">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                Encontramos <b>{{ count($pilotos) }} pilotos</b>. Eliminá las filas que no correspondan (ej: auto de seguridad, pace car) usando el botón quitar. Podés hacer click en cualquier celda para editarla.
            </div>
        </div>

        <form action="{{ route('admin.pilotos.import.store') }}" method="POST">
            @csrf
            <div class="rev-tbl">
                <table>
                    <thead>
                        <tr>
                            <th width="10%">Orden</th>
                            <th width="15%">N° Auto</th>
                            <th>Piloto</th>
                            <th width="20%">País</th>
                            <th width="15%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pilotos as $index => $p)
                        <tr id="row-{{ $index }}">
                            <td><input type="number" name="pilotos[{{ $index }}][orden]" value="{{ $p['orden'] ?? '' }}" readonly style="color:var(--gray);"></td>
                            <td><input type="number" name="pilotos[{{ $index }}][auto]" value="{{ $p['auto'] ?? '' }}"></td>
                            <td><input type="text" name="pilotos[{{ $index }}][nombre]" value="{{ $p['nombre'] ?? '' }}"></td>
                            <td><input type="text" name="pilotos[{{ $index }}][pais]" value="{{ $p['pais'] ?? 'Argentina' }}" placeholder="País"></td>
                            <td style="text-align:right;">
                                <button type="button" class="rev-del" onclick="document.getElementById('row-{{ $index }}').remove()">Quitar</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="review-actions">
                <a href="{{ route('admin.pilotos.import.form') }}" class="btn ghost">Cancelar</a>
                <button type="submit" class="btn">Confirmar y Guardar</button>
            </div>
        </form>
    </div>
</section>
@endsection
