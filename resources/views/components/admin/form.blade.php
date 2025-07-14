<div class="card-modern">
    <div class="card-header-modern">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
    </div>
    <div class="card-body-modern">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method ?? false)
            @method($method)
            @endif

            <div class="row g-3">
                @foreach($fields as $field)
                <div class="col-12 col-sm-6 col-md-{{ $field['width'] ?? 12 }} col-lg-{{ $field['width'] ?? 12 }}">
                    <div class="form-field-container">
                        <label for="{{ $field['name'] }}" class="form-label fw-medium mb-2"
                            style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                            {{ $field['label'] }}
                            @if($field['required'] ?? false)
                            <span style="color: hsl(var(--accent));">*</span>
                            @endif
                        </label>

                        @if($field['type'] === 'text' || $field['type'] === 'email' || $field['type'] === 'number')
                        <input type="{{ $field['type'] }}"
                            class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            value="{{ old($field['name'], $field['value'] ?? '') }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                            @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                            @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>

                        @elseif($field['type'] === 'textarea')
                        <textarea class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            rows="{{ $field['rows'] ?? 3 }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            style="resize: vertical; min-height: 80px;"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old($field['name'], $field['value'] ?? '') }}</textarea>

                        @elseif($field['type'] === 'select')
                        <select class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            <option value="">Seleccionar...</option>
                            @if(isset($field['options']))
                                @foreach($field['options'] as $option)
                                    @php
                                        $optionValue = is_object($option) ? $option->{$field['optionValue'] ?? 'id'} : (is_array($option) ? $option[$field['optionValue'] ?? 'id'] : $option);
                                        $optionLabel = is_object($option) ? $option->{$field['optionLabel'] ?? 'name'} : (is_array($option) ? $option[$field['optionLabel'] ?? 'name'] : $option);
                                    @endphp
                                    <option value="{{ $optionValue }}"
                                        {{ old($field['name'], $field['value'] ?? '') == $optionValue ? 'selected' : '' }}>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            @endif
                        </select>

                        @elseif($field['type'] === 'searchable_select')
                        <select class="input-modern searchable-select @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            data-search-url="{{ $field['search_url'] }}"
                            data-preview-url="{{ $field['preview_url'] ?? '' }}"
                            data-placeholder="{{ $field['placeholder'] ?? 'Buscar...' }}"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @if(!empty($field['value']))
                            <option value="{{ $field['value'] }}" selected>{{ $field['selected_text'] ?? '' }}</option>
                            @endif
                        </select>

                        @elseif($field['type'] === 'date')
                        <input type="date"
                            class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            value="{{ old($field['name'], $field['value'] ?? '') }}"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            
                        @elseif($field['type'] === 'time')
                        <input type="time"
                            class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            value="{{ old($field['name'], $field['value'] ?? '') }}"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>

                        @elseif($field['type'] === 'checkbox')
                        <div class="form-check-container">
                            <div class="d-flex align-items-center">
                                <input type="checkbox"
                                    class="form-check-input @error($field['name']) border-danger @enderror"
                                    id="{{ $field['name'] }}"
                                    name="{{ $field['name'] }}"
                                    value="1"
                                    style="width: 1.125rem; height: 1.125rem; margin-right: 0.75rem; accent-color: hsl(var(--accent));"
                                    {{ old($field['name'], $field['value'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-normal" for="{{ $field['name'] }}"
                                    style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                                    {{ $field['checkboxLabel'] ?? $field['label'] }}
                                </label>
                            </div>
                        </div>

                        @elseif($field['type'] === 'file')
                        <input type="file"
                            class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            @if(isset($field['accept'])) accept="{{ $field['accept'] }}" @endif
                            @if(isset($field['multiple']) && $field['multiple']) multiple @endif
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                        @endif

                        @error($field['name'])
                        <div class="error-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                        @enderror

                        @if(isset($field['help']))
                        <small class="form-help">
                            {{ $field['help'] }}
                        </small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="fas fa-save me-2"></i> {{ $submitText ?? 'Guardar' }}
                </button>
                <a href="{{ $cancelRoute }}" class="btn-modern btn-secondary-modern">
                    <i class="fas fa-arrow-left me-2"></i> {{ $cancelText ?? 'Cancelar' }}
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* Form Component Responsive Styles */
.form-field-container {
    margin-bottom: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    margin-top: 1rem;
    border-top: 1px solid hsl(var(--border));
    flex-wrap: wrap;
}

.error-feedback {
    color: hsl(var(--destructive));
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
}

.form-help {
    color: hsl(var(--muted-foreground));
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
    line-height: 1.4;
}

.form-check-container {
    padding: 0.5rem 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-field-container {
        margin-bottom: 1.25rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .form-actions .btn-modern {
        width: 100%;
        justify-content: center;
    }
    
    .input-modern {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}

@media (max-width: 576px) {
    .card-body-modern {
        padding: 1rem;
    }
    
    .form-field-container {
        margin-bottom: 1rem;
    }
    
    .form-actions {
        padding-top: 1rem;
    }
    
    .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.375rem;
    }
    
    .input-modern {
        padding: 0.625rem 0.75rem;
        font-size: 16px;
    }
    
    .btn-modern {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
}

/* Bootstrap grid responsive adjustments */
.row.g-3 {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

@media (max-width: 576px) {
    .row.g-3 {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }
}

/* Ensure proper column behavior on small screens */
@media (max-width: 576px) {
    .col-sm-6[class*="col-md-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar selects buscables
        $('.searchable-select').each(function() {
            const $select = $(this);
            const searchUrl = $select.data('search-url');
            const placeholder = $select.data('placeholder') || 'Buscar...';

            const select2Config = {
                placeholder: placeholder,
                allowClear: true,
                minimumInputLength: 0,
                width: '100%', // Ensure full width
                ajax: {
                    url: searchUrl,
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            limit: params.term ? 20 : 5,
                            preview: !params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumResultsForSearch: 0,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    inputTooShort: function() {
                        return "Escribe para buscar más opciones";
                    }
                },
                // Responsive dropdown
                dropdownParent: $select.closest('.form-field-container')
            };

            $select.select2(select2Config);

            // Handle responsive behavior
            $(window).on('resize', function() {
                $select.select2('close');
            });
        });

        // Form validation enhancements
        $('form').on('submit', function(e) {
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            // Add loading state
            $submitBtn.addClass('btn-loading').prop('disabled', true);
            
            // Remove loading state after a delay (in case of validation errors)
            setTimeout(function() {
                $submitBtn.removeClass('btn-loading').prop('disabled', false);
            }, 3000);
        });

        // Auto-resize textareas
        $('textarea.input-modern').each(function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        }).on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
</script>
@endpush
