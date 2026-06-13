{{-- Modal para crear nueva fecha --}}
<div class="custom-modal" id="{{ $modalId }}">
    <div class="custom-modal-content">
        <form id="createFechaForm" action="{{ $formAction }}" method="POST">
            @csrf
            <input type="hidden" name="campeonato_id" value="{{ $additionalData['campeonato_id'] ?? '' }}">
            
            <div class="custom-modal-header">
                <h5 class="custom-modal-title">
                    <x-heroicon-o-calendar-days style="width:1em; height:1em; vertical-align:-0.125em; margin-right: 8px;" />
                    {{ $title }}
                </h5>
                <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="custom-modal-body">
                <div class="fgrid">
                    {{-- Nombre de la fecha --}}
                    <div class="field full">
                        <label for="nombre">Nombre de la Fecha</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Primera Fecha, Fecha Final, etc." required style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                        <div class="invalid-feedback" id="nombre-error" style="display: none; color: var(--racing); font-size: 12px; margin-top: 4px;"></div>
                    </div>

                    {{-- Fecha desde --}}
                    <div class="field" style="grid-column: span 6;">
                        <label for="fecha_desde">Fecha Desde</label>
                        <input type="date" id="fecha_desde" name="fecha_desde" required style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                        <div class="invalid-feedback" id="fecha_desde-error" style="display: none; color: var(--racing); font-size: 12px; margin-top: 4px;"></div>
                    </div>

                    {{-- Fecha hasta --}}
                    <div class="field" style="grid-column: span 6;">
                        <label for="fecha_hasta">Fecha Hasta</label>
                        <input type="date" id="fecha_hasta" name="fecha_hasta" required style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                        <div class="invalid-feedback" id="fecha_hasta-error" style="display: none; color: var(--racing); font-size: 12px; margin-top: 4px;"></div>
                    </div>

                    {{-- Circuito --}}
                    <div class="field full">
                        <label for="circuito_id">Circuito</label>
                        <select id="circuito_id" name="circuito_id" required style="width: 100%; background: var(--black); border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);">
                            <option value="">Seleccionar circuito...</option>
                            @if(isset($additionalData['circuitos']))
                                @foreach($additionalData['circuitos'] as $circuito)
                                    <option value="{{ $circuito->id }}">{{ $circuito->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="invalid-feedback" id="circuito_id-error" style="display: none; color: var(--racing); font-size: 12px; margin-top: 4px;"></div>
                    </div>

                    {{-- Descripción (opcional) --}}
                    <div class="field full">
                        <label for="descripcion">Descripción <span style="color: var(--gray); font-size: 11px;">(opcional)</span></label>
                        <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción adicional de la fecha..." style="width: 100%; background: transparent; border: 1px solid var(--line); color: var(--bone); padding: 8px; font-family: var(--font-sans);"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="custom-modal-footer">
                <button type="button" class="btn ghost" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn" style="background: var(--white); color: var(--black);" id="submitBtn">
                    <x-heroicon-o-document-check style="width:1em; height:1em; vertical-align:-0.125em;" /> Crear Fecha
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createFechaForm');
    const submitBtn = document.getElementById('submitBtn');
    const modal = document.getElementById('{{ $modalId }}');
    
    // Abrir modal manualmente si alguien usa data-bs-target (como Bootstrap)
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-bs-target="#{{ $modalId }}"]');
        if (toggleBtn) {
            e.preventDefault();
            form.reset();
            clearErrors();
            modal.style.display = 'flex';
        }
    });

    // Validar fechas
    const fechaDesde = document.getElementById('fecha_desde');
    const fechaHasta = document.getElementById('fecha_hasta');
    
    fechaDesde.addEventListener('change', function() {
        if (fechaHasta.value && fechaDesde.value > fechaHasta.value) {
            fechaHasta.value = fechaDesde.value;
        }
        fechaHasta.min = fechaDesde.value;
    });
    
    fechaHasta.addEventListener('change', function() {
        if (fechaDesde.value && fechaHasta.value < fechaDesde.value) {
            showError('fecha_hasta', 'La fecha hasta no puede ser anterior a la fecha desde');
            return;
        }
        clearError('fecha_hasta');
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        submitBtn.innerHTML = 'Guardando...';
        submitBtn.disabled = true;
        
        clearErrors();
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.style.display = 'none';
                
                alert('✓ Fecha creada exitosamente');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showError(field, data.errors[field][0]);
                    });
                } else {
                    alert('⚠ ' + (data.message || 'Error al crear la fecha'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('⚠ Error de conexión');
        })
        .finally(() => {
            submitBtn.innerHTML = '<x-heroicon-o-document-check style="width:1em; height:1em; vertical-align:-0.125em;" /> Crear Fecha';
            submitBtn.disabled = false;
        });
    });
    
    function showError(field, message) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + '-error');
        
        if (input && errorDiv) {
            input.style.borderColor = 'var(--racing)';
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
    
    function clearError(field) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + '-error');
        
        if (input && errorDiv) {
            input.style.borderColor = 'var(--line)';
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    }
    
    function clearErrors() {
        ['nombre', 'fecha_desde', 'fecha_hasta', 'circuito_id'].forEach(clearError);
    }
});
</script>