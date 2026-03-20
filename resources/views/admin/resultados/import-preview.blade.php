@extends('layouts.admin')

@section('title', 'Vista Previa Importación OCR/PDF')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Vista Previa de Resultados',
    'subtitle' => 'Sesión: ' . $sesion->tipo . ' - ' . ($sesion->fecha->nombre ?? 'Sin fecha')
])

@php
    $meta = [];
    $hasSectors = true; 
    $hasTiempoTotal = true;

    // Detectar si el último elemento es metadata
    if(count($resultados_json) > 0 && isset($resultados_json[count($resultados_json)-1]['_meta'])) {
        $metaObj = array_pop($resultados_json);
        $meta = $metaObj['_meta'];
        $hasSectors = $meta['hasSectors'] ?? false;
        $hasTiempoTotal = $meta['hasTiempoTotal'] ?? false;
    }
@endphp

<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">
            Revisar Resultados de Planilla/OCR
        </h5>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
            <i class="fas fa-magic me-1"></i> Detectados: {{ count($resultados_json) }}
        </span>
    </div>
    
    <div class="card-body-modern">
        <div class="alert alert-warning border-0 rounded-4">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Revisa con atención:</strong> La herramienta intenta asignar cada nombre escaneado a un "Piloto del Sistema". Verifica que cada fila esté asignada al piloto correcto y que los tiempos sean exactos.
        </div>

        <form action="{{ route('admin.resultados.import.store') }}" method="POST">
            @csrf
            <input type="hidden" name="sesion_id" value="{{ $sesion->id }}">

            <div class="table-responsive">
                <table class="table-modern table-modern-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%">Pos</th>
                            <th style="width: 5%">Auto</th>
                            <th style="width: 15%">Nombre PDF/OCR</th>
                            <th style="width: 15%">Vincular Piloto *</th>
                            @if($hasTiempoTotal)
                                <th style="width: 10%">Tiempo Total</th>
                            @endif
                            <th style="width: 10%">Mejor Tm</th>
                            <th style="width: 10%">Dif</th>
                            <th style="width: 5%">Vtas</th>
                            @if($hasSectors)
                                <th style="width: 8%">S1</th>
                                <th style="width: 8%">S2</th>
                                <th style="width: 8%">S3</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resultados_json as $index => $row)
                        <tr>
                            <td>
                                {{ $row['posicion'] ?? '' }}
                                <input type="hidden" name="items[{{ $index }}][posicion]" value="{{ $row['posicion'] ?? '' }}">
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $row['auto'] ?? '' }}</span>
                            </td>
                            <td>{{ $row['nombre'] ?? '' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <div id="match-icon-{{ $index }}">
                                        @if(empty($row['piloto_id_match']))
                                            <i class="fas fa-exclamation-triangle text-warning" title="No se encontró coincidencia exacta"></i>
                                        @else
                                            <i class="fas fa-check-circle text-success" title="Coincidencia encontrada"></i>
                                        @endif
                                    </div>
                                    <select name="items[{{ $index }}][piloto_id]" class="input-modern form-select-sm pilot-selector" required style="padding: 0.25rem 0.5rem; font-size: 0.875rem; min-width: 140px;" onchange="updateMatchIcon(this, {{ $index }})">
                                        <option value="">-- Buscar Piloto --</option>
                                        @foreach($pilotos as $p)
                                            <option value="{{ $p->id }}" {{ ($row['piloto_id_match'] ?? null) === $p->id ? 'selected' : '' }}>
                                                {{ $p->nombre }} 
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-primary p-1 border-0" title="Crear nuevo piloto" onclick="quickCreatePilot({{ $index }}, '{{ addslashes($row['nombre'] ?? '') }}', '{{ $row['auto'] ?? '' }}')">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            </td>
                            @if($hasTiempoTotal)
                                <td>
                                    <input type="text" name="items[{{ $index }}][tiempo_total]" class="input-modern px-2 py-1" value="{{ $row['tiempo_total'] ?? '' }}" placeholder="1:XX.XXX">
                                </td>
                            @else
                                <input type="hidden" name="items[{{ $index }}][tiempo_total]" value="">
                            @endif
                            
                            <td>
                                <input type="text" name="items[{{ $index }}][mejor_tiempo]" class="input-modern px-2 py-1" value="{{ $row['mejor_tiempo'] ?? '' }}" placeholder="1:XX.XXX">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][diferencia]" class="input-modern px-2 py-1" value="{{ $row['diferencia'] ?? '' }}" placeholder="0.XXX">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][vueltas]" class="input-modern px-2 py-1" value="{{ $row['vueltas'] ?? '' }}">
                            </td>
                            
                            @if($hasSectors)
                                <td>
                                    <input type="text" name="items[{{ $index }}][sector_1]" class="input-modern px-2 py-1" value="{{ $row['sector_1'] ?? '' }}" placeholder="2X.XXX">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][sector_2]" class="input-modern px-2 py-1" value="{{ $row['sector_2'] ?? '' }}" placeholder="2X.XXX">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][sector_3]" class="input-modern px-2 py-1" value="{{ $row['sector_3'] ?? '' }}" placeholder="2X.XXX">
                                </td>
                            @else
                                <input type="hidden" name="items[{{ $index }}][sector_1]" value="">
                                <input type="hidden" name="items[{{ $index }}][sector_2]" value="">
                                <input type="hidden" name="items[{{ $index }}][sector_3]" value="">
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 6 + ($hasTiempoTotal ? 1 : 0) + ($hasSectors ? 3 : 0) }}" class="text-center py-4 text-muted">No se extrajeron datos válidos. Intentá con otro archivo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="form-actions mt-4 pt-3 border-top">
                <button type="submit" class="btn-modern btn-primary-modern" {{ count($resultados_json) == 0 ? 'disabled' : '' }}>
                    <i class="fas fa-save me-2"></i> Confirmar y Guardar Resultados
                </button>
                <a href="{{ route('admin.resultados.import.form') }}" class="btn-modern btn-secondary-modern">
                    <i class="fas fa-arrow-left me-2"></i> Volver e Intentar de Nuevo
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* Ajustes finos para la tabla editable */
.table-responsive {
    cursor: grab;
    overflow-x: auto;
}
.table-responsive.active-drag {
    cursor: grabbing;
}
.table-modern th, .table-modern td {
    vertical-align: middle;
    white-space: nowrap;
}
.table-modern .input-modern {
    background: transparent;
    border: 1px solid transparent;
    border-bottom: 1px solid hsl(var(--border));
    border-radius: 0;
    transition: all 0.2s;
    text-align: center;
    width: 100%;
    min-width: 80px;
}
.table-modern .input-modern:focus {
    background: hsl(var(--background));
    border-color: hsl(var(--primary));
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.table-modern select.input-modern {
    border: 1px solid hsl(var(--border));
    border-radius: 0.375rem;
    text-align: left;
    min-width: 150px;
}
</style>

@push('scripts')
<script>
    // Lógica para drag-to-scroll en la tabla
    const slider = document.querySelector('.table-responsive');
    let isDown = false;
    let startX;
    let scrollLeft;

    if(slider) {
        slider.addEventListener('mousedown', (e) => {
            // Ignorar si hace click en un input, boton o select (interactuando con form)
            if (['INPUT', 'SELECT', 'BUTTON', 'A', 'I', 'LABEL', 'OPTION'].includes(e.target.tagName.toUpperCase())) return;
            
            isDown = true;
            slider.classList.add('active-drag');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active-drag');
        });
        
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active-drag');
        });
        
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.5; 
            slider.scrollLeft = scrollLeft - walk;
        });
    }

    function updateMatchIcon(select, index) {
        const iconDiv = document.getElementById(`match-icon-${index}`);
        if (!iconDiv) return;
        
        if (select.value === '') {
            iconDiv.innerHTML = '<i class="fas fa-exclamation-triangle text-warning" title="Seleccione un piloto"></i>';
        } else {
            iconDiv.innerHTML = '<i class="fas fa-check-circle text-success" title="Piloto vinculado"></i>';
        }
    }

    let currentQuickCreateIndex = null;
    let quickCreateModalObj = null;

    // Inicializar el modal cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        quickCreateModalObj = new bootstrap.Modal(document.getElementById('quickCreatePilotModal'));
    });

    function quickCreatePilot(index, suggestedName, suggestedNumber = null) {
        currentQuickCreateIndex = index;
        const nameInput = document.getElementById('new_piloto_nombre');
        const numberInput = document.getElementById('new_piloto_numero');
        const errorDiv = document.getElementById('quick-create-error');
        
        nameInput.value = suggestedName;
        numberInput.value = suggestedNumber || '';
        errorDiv.classList.add('d-none');
        
        if (quickCreateModalObj) {
            quickCreateModalObj.show();
            // Focus input after modal is shown
            setTimeout(() => nameInput.focus(), 500);
        }
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
            errorDiv.classList.remove('d-none');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creando...';

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
                errorDiv.classList.remove('d-none');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i> Crear y Vincular';
                return;
            }

            // Agregar el nuevo piloto a TODOS los selects de la página
            const selectors = document.querySelectorAll('.pilot-selector');
            selectors.forEach(sel => {
                const option = new Option(data.nombre, data.id);
                sel.add(option);
            });

            // Seleccionarlo para la fila actual
            const currentSelector = document.querySelectorAll('.pilot-selector')[currentQuickCreateIndex];
            currentSelector.value = data.id;
            updateMatchIcon(currentSelector, currentQuickCreateIndex);

            if (quickCreateModalObj) {
                quickCreateModalObj.hide();
            }

        } catch (error) {
            console.error(error);
            alert("Ocurrió un error en la conexión.");
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-1"></i> Crear y Vincular';
        }
    });
</script>
@endpush

<!-- Quick Create Pilot Modal -->
<div class="modal fade" id="quickCreatePilotModal" tabindex="-1" aria-labelledby="quickCreatePilotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-modern" style="border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="quickCreatePilotModalLabel">
                    <i class="fas fa-helmet-safety me-2 text-primary"></i> Crear Nuevo Piloto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Ingresa el nombre del piloto para crearlo en el sistema y vincularlo a este resultado.</p>
                
                <div class="mb-3">
                    <label for="new_piloto_nombre" class="form-label fw-medium">Nombre Completo *</label>
                    <input type="text" id="new_piloto_nombre" class="input-modern" placeholder="Ej: Santiago Villar" style="text-align: left;">
                    <div id="quick-create-error" class="text-danger small mt-1 d-none"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="new_piloto_campeonato" class="form-label fw-medium">Asignar a Campeonato</label>
                        <select id="new_piloto_campeonato" class="input-modern form-select" style="text-align: left;">
                            <option value="">-- No asignar --</option>
                            @foreach($campeonatos as $c)
                                <option value="{{ $c->id }}" {{ $c->id == ($sesion->fecha->campeonato_id ?? '') ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="new_piloto_numero" class="form-label fw-medium">N° Auto</label>
                        <input type="number" id="new_piloto_numero" class="input-modern" placeholder="0" style="text-align: left;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmQuickCreate" class="btn-modern btn-primary-modern">
                    <i class="fas fa-save me-1"></i> Crear y Vincular
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
