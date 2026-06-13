@extends('layouts.admin')
@section('title', 'Vista Previa Importación OCR/PDF')

@section('content')
<div class="view-head">
    <h1>VISTA PREVIA DE RESULTADOS <span class="lap">SESIÓN: {{ strtoupper($sesion->tipo) }} - {{ strtoupper($sesion->fecha->nombre ?? 'SIN FECHA') }}</span></h1>
</div>

@php
    $meta = [];
    $hasSectors = true; 
    $hasTiempoTotal = true;

    if(count($resultados_json) > 0 && isset($resultados_json[count($resultados_json)-1]['_meta'])) {
        $metaObj = array_pop($resultados_json);
        $meta = $metaObj['_meta'];
        $hasSectors = $meta['hasSectors'] ?? false;
        $hasTiempoTotal = $meta['hasTiempoTotal'] ?? false;
    }
@endphp

<div style="margin-bottom: 24px;">
    <div style="font-family:var(--font-sans); font-size:14px; font-weight:600; margin-bottom:8px;">Revisar Resultados de Planilla/OCR</div>
    <div style="display:inline-block; background:var(--carbon); border:1px solid var(--line); padding:4px 12px; font-size:12px; font-family:var(--font-display); letter-spacing:1px;"><x-heroicon-o-sparkles style="width:1em; height:1em; vertical-align:-0.125em; margin-right:6px;" /> DETECTADOS: {{ count($resultados_json) }}</div>
</div>

<div style="background:var(--carbon-2); border-left:3px solid var(--racing); padding:16px; margin-bottom:24px; font-family:var(--font-sans); font-size:14px; color:var(--white);">
    <x-heroicon-o-exclamation-triangle style="width:1em; height:1em; vertical-align:-0.125em; color:var(--racing); margin-right:8px;" />
    <strong>Revisa con atención:</strong> La herramienta intenta asignar cada nombre escaneado a un "Piloto del Sistema". Verifica que cada fila esté asignada al piloto correcto y que los tiempos sean exactos.
</div>

<form action="{{ route('admin.resultados.import.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sesion_id" value="{{ $sesion->id }}">

    <div class="tbl-wrap" style="border:none; box-shadow:none; background:transparent;">
        <table class="preview-tbl">
            <thead>
                <tr>
                    <th width="5%">POS</th>
                    <th width="5%">AUTO</th>
                    <th width="20%">NOMBRE PDF/OCR</th>
                    <th width="20%">VINCULAR PILOTO *</th>
                    @if($hasTiempoTotal)
                        <th width="10%">TIEMPO TOTAL</th>
                    @endif
                    <th width="10%">MEJOR TM</th>
                    <th width="10%">DIF</th>
                    <th width="5%">VTAS</th>
                    @if($hasSectors)
                        <th width="8%">S1</th>
                        <th width="8%">S2</th>
                        <th width="8%">S3</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($resultados_json as $index => $row)
                <tr>
                    <td style="font-weight:700;">
                        {{ $row['posicion'] ?? '' }}
                        <input type="hidden" name="items[{{ $index }}][posicion]" value="{{ $row['posicion'] ?? '' }}">
                    </td>
                    <td>
                        <span style="background:var(--carbon); border:1px solid var(--line); padding:4px 8px; font-size:12px;">{{ $row['auto'] ?? '' }}</span>
                    </td>
                    <td style="color:var(--gray);">{{ $row['nombre'] ?? '' }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div id="match-icon-{{ $index }}">
                                @if(empty($row['piloto_id_match']))
                                    <x-heroicon-o-exclamation-triangle style="width:1em; height:1em; vertical-align:-0.125em; color:var(--racing);" />
                                @else
                                    <x-heroicon-o-check-circle style="width:1em; height:1em; vertical-align:-0.125em; color:#22c55e;" />
                                @endif
                            </div>
                            <select name="items[{{ $index }}][piloto_id]" class="pilot-selector" required onchange="updateMatchIcon(this, {{ $index }})">
                                <option value="">-- Buscar Piloto --</option>
                                @foreach($pilotos as $p)
                                    <option value="{{ $p->id }}" {{ ($row['piloto_id_match'] ?? null) === $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }} 
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="add-btn" title="Crear nuevo piloto" onclick="quickCreatePilot({{ $index }}, '{{ addslashes($row['nombre'] ?? '') }}', '{{ $row['auto'] ?? '' }}')">
                                <x-heroicon-o-plus-circle style="width:1em; height:1em; vertical-align:-0.125em;" />
                            </button>
                        </div>
                    </td>
                    @if($hasTiempoTotal)
                        <td>
                            <input type="text" name="items[{{ $index }}][tiempo_total]" class="invisible-input" value="{{ $row['tiempo_total'] ?? '' }}" placeholder="1:XX.XXX">
                        </td>
                    @else
                        <input type="hidden" name="items[{{ $index }}][tiempo_total]" value="">
                    @endif
                    
                    <td>
                        <input type="text" name="items[{{ $index }}][mejor_tiempo]" class="invisible-input" value="{{ $row['mejor_tiempo'] ?? '' }}" placeholder="1:XX.XXX">
                    </td>
                    <td>
                        <input type="text" name="items[{{ $index }}][diferencia]" class="invisible-input" value="{{ $row['diferencia'] ?? '' }}" placeholder="0.XXX">
                    </td>
                    <td>
                        <input type="number" name="items[{{ $index }}][vueltas]" class="invisible-input" value="{{ $row['vueltas'] ?? '' }}">
                    </td>
                    
                    @if($hasSectors)
                        <td>
                            <input type="text" name="items[{{ $index }}][sector_1]" class="invisible-input" value="{{ $row['sector_1'] ?? '' }}" placeholder="2X.XXX">
                        </td>
                        <td>
                            <input type="text" name="items[{{ $index }}][sector_2]" class="invisible-input" value="{{ $row['sector_2'] ?? '' }}" placeholder="2X.XXX">
                        </td>
                        <td>
                            <input type="text" name="items[{{ $index }}][sector_3]" class="invisible-input" value="{{ $row['sector_3'] ?? '' }}" placeholder="2X.XXX">
                        </td>
                    @else
                        <input type="hidden" name="items[{{ $index }}][sector_1]" value="">
                        <input type="hidden" name="items[{{ $index }}][sector_2]" value="">
                        <input type="hidden" name="items[{{ $index }}][sector_3]" value="">
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 6 + ($hasTiempoTotal ? 1 : 0) + ($hasSectors ? 3 : 0) }}" style="text-align:center; padding:30px; color:var(--gray);">No se extrajeron datos válidos. Intentá con otro archivo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--line); display:flex; gap:12px; justify-content:flex-end;">
        <a href="{{ route('admin.resultados.import.form') }}" class="btn ghost">Cancelar</a>
        <button type="submit" class="btn" style="background:var(--white); color:var(--black);" {{ count($resultados_json) == 0 ? 'disabled' : '' }}>
            CONFIRMAR Y GUARDAR RESULTADOS
        </button>
    </div>
</form>

<style>
.preview-tbl { width: 100%; border-collapse: collapse; }
.preview-tbl thead th { background: var(--white); color: var(--black); font-family: var(--font-display); font-size: 14px; font-weight: 700; padding: 12px 16px; text-transform: uppercase; text-align: left; }
.preview-tbl tbody td { border-bottom: 1px solid var(--line); padding: 12px 16px; font-family: var(--font-sans); font-size: 13px; color: var(--bone); vertical-align: middle; }
.preview-tbl tbody tr { background: #131313; transition: 0.2s; }
.preview-tbl tbody tr:nth-child(even) { background: #181818; }
.preview-tbl tbody tr:hover { background: #1f1f1f; }

.pilot-selector { background: transparent; border: 1px solid var(--gray); color: var(--white); font-family: var(--font-sans); font-size: 13px; padding: 6px 12px; border-radius: 4px; width: 180px; outline: none; }
.pilot-selector:focus { border-color: var(--white); }
.pilot-selector option { background: var(--carbon); color: var(--white); }

.add-btn { background: var(--white); border: none; width: 28px; height: 28px; border-radius: 2px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
.add-btn i { color: var(--black); font-size: 14px; }
.add-btn:hover { background: var(--gray); }

.invisible-input { background: transparent; border: none; color: var(--gray); font-family: var(--font-sans); font-size: 13px; width: 100%; min-width: 60px; outline: none; transition: 0.2s; }
.invisible-input:focus { color: var(--white); border-bottom: 1px solid var(--gray); }

/* Modal Quick Create */
.qc-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; display:none; align-items:center; justify-content:center; }
.qc-modal { background: var(--black); border: 1px solid var(--line); width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
.qc-title { font-family: var(--font-display); font-size: 24px; color: var(--white); margin-bottom: 8px; text-transform: uppercase; }
.qc-sub { font-family: var(--font-sans); font-size: 13px; color: var(--gray); margin-bottom: 24px; }
</style>

<div class="qc-overlay" id="quickCreateModal">
    <div class="qc-modal">
        <div class="qc-title">Crear Nuevo Piloto</div>
        <div class="qc-sub">Ingresa el nombre del piloto para crearlo en el sistema y vincularlo a este resultado.</div>
        
        <div class="fgrid" style="display:grid; grid-template-columns:1fr; gap:16px;">
            <div class="field">
                <label>Nombre Completo *</label>
                <input type="text" id="new_piloto_nombre" placeholder="Ej: Santiago Villar">
                <div id="quick-create-error" style="color:var(--racing); font-size:12px; margin-top:4px; display:none;"></div>
            </div>
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px;">
                <div class="field">
                    <label>Campeonato</label>
                    <select id="new_piloto_campeonato">
                        <option value="">-- No asignar --</option>
                        @foreach($campeonatos as $c)
                            <option value="{{ $c->id }}" {{ $c->id == ($sesion->fecha->campeonato_id ?? '') ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>N° Auto</label>
                    <input type="number" id="new_piloto_numero" placeholder="0">
                </div>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; justify-content:flex-end; gap:12px;">
            <button class="btn ghost" onclick="document.getElementById('quickCreateModal').style.display='none'">Cancelar</button>
            <button class="btn" id="confirmQuickCreate" style="background:var(--white); color:var(--black);">Crear y Vincular</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateMatchIcon(select, index) {
        const iconDiv = document.getElementById(`match-icon-${index}`);
        if (!iconDiv) return;
        
        if (select.value === '') {
            iconDiv.innerHTML = '<x-heroicon-o-exclamation-triangle style="width:1em; height:1em; vertical-align:-0.125em; color:var(--racing);" />';
        } else {
            iconDiv.innerHTML = '<x-heroicon-o-check-circle style="width:1em; height:1em; vertical-align:-0.125em; color:#22c55e;" />';
        }
    }

    let currentQuickCreateIndex = null;

    function quickCreatePilot(index, suggestedName, suggestedNumber = null) {
        currentQuickCreateIndex = index;
        document.getElementById('new_piloto_nombre').value = suggestedName;
        document.getElementById('new_piloto_numero').value = suggestedNumber || '';
        document.getElementById('quick-create-error').style.display = 'none';
        
        document.getElementById('quickCreateModal').style.display = 'flex';
        setTimeout(() => document.getElementById('new_piloto_nombre').focus(), 100);
    }

    document.getElementById('confirmQuickCreate').addEventListener('click', async function() {
        const nameInput = document.getElementById('new_piloto_nombre');
        const numberInput = document.getElementById('new_piloto_numero');
        const campSelect = document.getElementById('new_piloto_campeonato');
        const errorDiv = document.getElementById('quick-create-error');
        
        const name = nameInput.value.trim();
        const number = numberInput.value.trim();
        const campId = campSelect.value;
        
        if (!name) {
            errorDiv.textContent = "El nombre es obligatorio.";
            errorDiv.style.display = 'block';
            return;
        }

        this.disabled = true;
        this.innerHTML = 'Creando...';

        try {
            const response = await fetch('{{ route("admin.pilotos.quick-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    nombre: name,
                    campeonato_id: campId,
                    numero_auto: number
                })
            });

            const data = await response.json();

            if (!response.ok) {
                errorDiv.textContent = data.message || "Error al crear piloto.";
                errorDiv.style.display = 'block';
                this.disabled = false;
                this.innerHTML = 'Crear y Vincular';
                return;
            }

            const selectors = document.querySelectorAll('.pilot-selector');
            selectors.forEach(sel => {
                const option = new Option(data.nombre, data.id);
                sel.add(option);
            });

            const currentSelector = document.querySelectorAll('.pilot-selector')[currentQuickCreateIndex];
            currentSelector.value = data.id;
            updateMatchIcon(currentSelector, currentQuickCreateIndex);

            document.getElementById('quickCreateModal').style.display = 'none';

        } catch (error) {
            console.error(error);
            alert("Ocurrió un error en la conexión.");
        } finally {
            this.disabled = false;
            this.innerHTML = 'Crear y Vincular';
        }
    });
</script>
@endpush
@endsection
