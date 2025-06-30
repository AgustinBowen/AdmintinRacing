{{-- Modal para crear nueva fecha --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createFechaForm" action="{{ $formAction }}" method="POST">
                @csrf
                <input type="hidden" name="campeonato_id" value="{{ $additionalData['campeonato_id'] ?? '' }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        <i class="fas fa-calendar-plus"></i>
                        {{ $title }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Nombre de la fecha --}}
                        <div class="col-12">
                            <label for="nombre" class="form-label fw-semibold">
                                <i class="fas fa-tag me-1" style="color: hsl(var(--primary));"></i>
                                Nombre de la Fecha
                            </label>
                            <input type="text" 
                                   class="input-modern" 
                                   id="nombre" 
                                   name="nombre" 
                                   placeholder="Ej: Primera Fecha, Fecha Final, etc."
                                   required>
                            <div class="invalid-feedback" id="nombre-error"></div>
                        </div>

                        {{-- Fecha desde --}}
                        <div class="col-md-6">
                            <label for="fecha_desde" class="form-label fw-semibold">
                                <i class="fas fa-calendar-day me-1" style="color: hsl(var(--primary));"></i>
                                Fecha Desde
                            </label>
                            <input type="date" 
                                   class="input-modern" 
                                   id="fecha_desde" 
                                   name="fecha_desde" 
                                   required>
                            <div class="invalid-feedback" id="fecha_desde-error"></div>
                        </div>

                        {{-- Fecha hasta --}}
                        <div class="col-md-6">
                            <label for="fecha_hasta" class="form-label fw-semibold">
                                <i class="fas fa-calendar-day me-1" style="color: hsl(var(--primary));"></i>
                                Fecha Hasta
                            </label>
                            <input type="date" 
                                   class="input-modern" 
                                   id="fecha_hasta" 
                                   name="fecha_hasta" 
                                   required>
                            <div class="invalid-feedback" id="fecha_hasta-error"></div>
                        </div>

                        {{-- Circuito --}}
                        <div class="col-12">
                            <label for="circuito_id" class="form-label fw-semibold">
                                <i class="fas fa-road me-1" style="color: hsl(var(--primary));"></i>
                                Circuito
                            </label>
                            <select class="input-modern" id="circuito_id" name="circuito_id" required>
                                <option value="">Seleccionar circuito...</option>
                                @if(isset($additionalData['circuitos']))
                                    @foreach($additionalData['circuitos'] as $circuito)
                                        <option value="{{ $circuito->id }}">{{ $circuito->nombre }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback" id="circuito_id-error"></div>
                        </div>

                        {{-- Descripción (opcional) --}}
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1" style="color: hsl(var(--primary));"></i>
                                Descripción <span class="text-muted">(opcional)</span>
                            </label>
                            <textarea class="input-modern" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Descripción adicional de la fecha..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-modern btn-primary-modern" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Crear Fecha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createFechaForm');
    const submitBtn = document.getElementById('submitBtn');
    const modal = document.getElementById('{{ $modalId }}');
    
    // Limpiar formulario al abrir el modal
    modal.addEventListener('show.bs.modal', function() {
        form.reset();
        clearErrors();
    });
    
    // Validación de fechas
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
    
    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar estado de carga
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        
        // Limpiar errores previos
        clearErrors();
        
        // Enviar formulario
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
                // Cerrar modal
                bootstrap.Modal.getInstance(modal).hide();
                
                // Mostrar mensaje de éxito y recargar página
                showToast('Fecha creada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                // Mostrar errores de validación
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showError(field, data.errors[field][0]);
                    });
                }
                showToast(data.message || 'Error al crear la fecha', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error de conexión', 'error');
        })
        .finally(() => {
            // Remover estado de carga
            submitBtn.classList.remove('btn-loading');
            submitBtn.disabled = false;
        });
    });
    
    function showError(field, message) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + '-error');
        
        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
    
    function clearError(field) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + '-error');
        
        if (input && errorDiv) {
            input.classList.remove('is-invalid');
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    }
    
    function clearErrors() {
        const inputs = form.querySelectorAll('.input-modern');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        const errorDivs = form.querySelectorAll('.invalid-feedback');
        errorDivs.forEach(div => {
            div.textContent = '';
            div.style.display = 'none';
        });
    }
    
    function showToast(message, type = 'success') {
        // Implementar sistema de toast/notificaciones
        // Por ahora usamos alert simple
        if (type === 'success') {
            alert('✓ ' + message);
        } else {
            alert('⚠ ' + message);
        }
    }
});
</script>

<style>
.form-label {
    color: hsl(var(--foreground));
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.text-muted {
    color: hsl(var(--muted-foreground)) !important;
    font-size: 0.8rem;
}

.input-modern.is-invalid {
    border-color: hsl(var(--destructive));
    box-shadow: 0 0 0 2px hsl(var(--destructive) / 0.2);
}

.invalid-feedback {
    display: none;
    color: hsl(var(--destructive));
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.g-3 > * {
    margin-bottom: 1rem;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 0.75rem;
    padding-left: 0.75rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>