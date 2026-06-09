<div class="form-card">
    <h1>{{ $title }}</h1>
    <div class="sub">CARGA MANUAL DE REGISTRO</div>

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($method ?? false)
        @method($method)
        @endif

        <div class="fgrid">
            @foreach($fields as $field)
            @php 
               $colSpan = ($field['width'] ?? 12) == 12 ? 'full' : '';
            @endphp
            <div class="field {{ $colSpan }}">
                <label for="{{ $field['name'] }}">
                    {{ $field['label'] }}
                    @if($field['required'] ?? false) <span style="color: var(--racing);">*</span> @endif
                </label>

                @if($field['type'] === 'text' || $field['type'] === 'email' || $field['type'] === 'number')
                <input type="{{ $field['type'] }}"
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    value="{{ old($field['name'], $field['value'] ?? '') }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                    @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                    @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    @error($field['name']) style="border-color: var(--racing);" @enderror>

                @elseif($field['type'] === 'textarea')
                <textarea
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    rows="{{ $field['rows'] ?? 3 }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    @error($field['name']) style="border-color: var(--racing);" @enderror>{{ old($field['name'], $field['value'] ?? '') }}</textarea>

                @elseif($field['type'] === 'select' || $field['type'] === 'searchable_select')
                <select class="{{ $field['type'] === 'searchable_select' ? 'searchable-select' : '' }}"
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    @error($field['name']) style="border-color: var(--racing);" @enderror
                    @if($field['type'] === 'searchable_select')
                        data-search-url="{{ $field['search_url'] ?? '' }}"
                        data-placeholder="{{ $field['placeholder'] ?? 'Buscar...' }}"
                    @endif>
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
                    @elseif(!empty($field['value']))
                        <option value="{{ $field['value'] }}" selected>{{ $field['selected_text'] ?? '' }}</option>
                    @endif
                </select>

                @elseif($field['type'] === 'date' || $field['type'] === 'time')
                <input type="{{ $field['type'] }}"
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    value="{{ old($field['name'], $field['value'] ?? '') }}"
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    @error($field['name']) style="border-color: var(--racing);" @enderror>

                @elseif($field['type'] === 'checkbox')
                <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
                    <input type="checkbox"
                        id="{{ $field['name'] }}"
                        name="{{ $field['name'] }}"
                        value="1"
                        style="width: 18px; height: 18px; accent-color: var(--white); background: var(--carbon); border: 1px solid var(--line);"
                        {{ old($field['name'], $field['value'] ?? false) ? 'checked' : '' }}>
                    <label for="{{ $field['name'] }}" style="margin:0; text-transform:none; letter-spacing:0; color:var(--bone); font-size:14px; font-weight:500;">
                        {{ $field['checkboxLabel'] ?? $field['label'] }}
                    </label>
                </div>

                @elseif($field['type'] === 'file')
                <input type="file"
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    @if(isset($field['accept'])) accept="{{ $field['accept'] }}" @endif
                    @if(isset($field['multiple']) && $field['multiple']) multiple @endif
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    @error($field['name']) style="border-color: var(--racing);" @enderror>
                @endif

                @error($field['name'])
                <div class="text-danger" style="font-size:12px; margin-top:6px;">
                    {{ $message }}
                </div>
                @enderror

                @if(isset($field['help']))
                <small class="form-help">{{ $field['help'] }}</small>
                @endif
            </div>
            @endforeach
        </div>

        <div class="form-actions">
            <a href="{{ $cancelRoute }}" class="btn ghost">{{ $cancelText ?? 'CANCELAR' }}</a>
            <button type="submit" class="btn" style="background:var(--white); color:var(--black);">{{ $submitText ?? 'GUARDAR REGISTRO' }}</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if($('.searchable-select').length) {
            $('.searchable-select').each(function() {
                const $select = $(this);
                const searchUrl = $select.data('search-url');
                const placeholder = $select.data('placeholder') || 'Buscar...';

                $select.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    ajax: searchUrl ? {
                        url: searchUrl,
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return { q: params.term || '', limit: 20 };
                        },
                        processResults: function(data) {
                            return { results: data.results };
                        }
                    } : null
                });
            });
        }
        
        $('textarea').each(function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        }).on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
</script>
@endpush
