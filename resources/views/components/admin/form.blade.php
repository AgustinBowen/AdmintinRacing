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

            <div class="row">
                @foreach($fields as $field)
                <div class="col-md-{{ $field['width'] ?? 12 }}">
                    <div class="mb-4">
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
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>

                        @elseif($field['type'] === 'textarea')
                        <textarea class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            rows="{{ $field['rows'] ?? 3 }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            style="resize: vertical;"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old($field['name'], $field['value'] ?? '') }}</textarea>

                        @elseif($field['type'] === 'select')
                        <select class="input-modern @error($field['name']) border-danger @enderror"
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            <option value="">Seleccionar...</option>
                            @foreach($field['options'] as $value => $label)
                            <option value="{{ $value }}"
                                {{ old($field['name'], $field['value'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
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
                        <div class="form-check" style="padding-left: 0;">
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
                        @endif

                        @error($field['name'])
                        <div class="mt-1" style="color: hsl(var(--accent)); font-size: 0.75rem;">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                        @enderror

                        @if(isset($field['help']))
                        <small class="form-text mt-1 d-block" style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;">
                            {{ $field['help'] }}
                        </small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-3 pt-4 mt-2" style="border-top: 1px solid hsl(var(--border));">
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
                minimumInputLength: 0, // Permitir mostrar resultados sin escribir
                ajax: {
                    url: searchUrl,
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            limit: params.term ? 20 : 5, // 5 elementos para preview, 20 para búsqueda
                            preview: !params.term // Indicar si es preview
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                // Configuración para mostrar resultados al abrir sin escribir
                minimumResultsForSearch: 0,
                // Personalizar el mensaje cuando no hay resultados
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
                }
            };

            $select.select2(select2Config);

            // Evento para cargar preview al abrir el dropdown
            $select.on('select2:open', function() {
                const select2 = $(this).data('select2');
                if (select2.$dropdown) {
                    // Si no hay opciones cargadas, ejecutar búsqueda vacía para mostrar preview
                    if (select2.$results.find('.select2-results__option').length === 0) {
                        select2.dataAdapter.query({
                            term: ''
                        }, function(data) {
                            select2.dataAdapter.displayData(data);
                        });
                    }
                }
            });
        });
    });
</script>
@endpush