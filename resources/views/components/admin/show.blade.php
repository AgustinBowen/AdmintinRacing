<div class="card-modern">
    <div class="card-header-modern d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
        @if(isset($headerActions))
            <div class="d-flex flex-wrap gap-2">
                @foreach($headerActions as $action)
                    <a href="{{ $action['route'] }}" 
                       class="btn-modern {{ $action['class'] ?? 'btn-secondary-modern' }}"
                       title="{{ $action['title'] ?? '' }}">
                        @if(isset($action['icon']))
                            <i class="{{ $action['icon'] }} me-1"></i>
                        @endif
                        <span class="d-none d-sm-inline">{{ $action['label'] }}</span>
                        <span class="d-sm-none">{{ $action['shortLabel'] ?? $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card-body-modern">
        <div class="row g-3 g-md-4">
            @foreach($fields as $field)
                <div class="col-12 col-sm-6 col-md-{{ $field['width'] ?? 12 }} col-lg-{{ $field['width'] ?? 12 }}">
                    <div class="show-field-wrapper">
                        <label class="show-field-label">
                            @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }} me-2 field-icon"></i>
                            @endif
                            {{ $field['label'] }}
                        </label>
                        
                        <div class="show-field-value">
                            @if($field['type'] === 'text' || $field['type'] === 'email' || $field['type'] === 'number')
                                <div class="show-text-field">
                                    {{ $field['value'] ?? '—' }}
                                </div>
                            
                            @elseif($field['type'] === 'textarea')
                                <div class="show-textarea-field">
                                    {!! nl2br(e($field['value'] ?? '—')) !!}
                                </div>
                            
                            @elseif($field['type'] === 'date')
                                <div class="show-date-field">
                                    @if($field['value'])
                                        @php
                                            $date = \Carbon\Carbon::parse($field['value']);
                                        @endphp
                                        <span class="date-value">
                                            {{ $date->format($field['format'] ?? 'd/m/Y') }}
                                        </span>
                                        @if($field['showRelative'] ?? false)
                                            <small class="text-muted d-block d-sm-inline ms-sm-2">
                                                ({{ $date->diffForHumans() }})
                                            </small>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </div>
                            
                            @elseif($field['type'] === 'time')
                                <div class="show-time-field">
                                    @if($field['value'])
                                        @php
                                            $time = \Carbon\Carbon::parse($field['value']);
                                        @endphp
                                        {{ $time->format($field['format'] ?? 'H:i') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            
                            @elseif($field['type'] === 'boolean')
                                <div class="show-boolean-field">
                                    @php
                                        $isTrue = $field['value'] ?? false;
                                        $badgeClass = $isTrue ? 'badge-success' : 'badge-secondary';
                                        $icon = $isTrue ? 'fas fa-check' : 'fas fa-times';
                                        $text = $isTrue ? ($field['trueText'] ?? 'Sí') : ($field['falseText'] ?? 'No');
                                    @endphp
                                    <span class="badge-modern {{ $badgeClass }}">
                                        <i class="{{ $icon }} me-1"></i>
                                        {{ $text }}
                                    </span>
                                </div>
                            
                            @elseif($field['type'] === 'badge')
                                <div class="show-badge-field">
                                    @php
                                        $badgeClass = match($field['color'] ?? 'primary') {
                                            'success' => 'badge-success',
                                            'danger' => 'badge-destructive',
                                            'warning' => 'badge-warning',
                                            'secondary' => 'badge-secondary',
                                            default => 'badge-primary'
                                        };
                                    @endphp
                                    <span class="badge-modern {{ $badgeClass }}">
                                        @if(isset($field['icon']))
                                            <i class="{{ $field['icon'] }} me-1"></i>
                                        @endif
                                        {{ $field['value'] ?? '—' }}
                                    </span>
                                </div>
                            
                            @elseif($field['type'] === 'image')
                                <div class="show-image-field">
                                    @if($field['value'])
                                        <img src="{{ $field['value'] }}" 
                                             alt="{{ $field['alt'] ?? $field['label'] }}"
                                             class="show-image img-fluid"
                                             style="max-width: {{ $field['maxWidth'] ?? '200px' }}; height: auto; border-radius: var(--radius); border: 1px solid hsl(var(--border));">
                                    @else
                                        <div class="show-image-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>Sin imagen</p>
                                        </div>
                                    @endif
                                </div>
                            
                            @elseif($field['type'] === 'link')
                                <div class="show-link-field">
                                    @if($field['value'])
                                        <a href="{{ $field['url'] ?? $field['value'] }}" 
                                           class="show-link"
                                           target="{{ $field['target'] ?? '_blank' }}"
                                           rel="noopener noreferrer">
                                            @if(isset($field['icon']))
                                                <i class="{{ $field['icon'] }} me-1"></i>
                                            @endif
                                            <span class="link-text">{{ $field['linkText'] ?? $field['value'] }}</span>
                                            @if(($field['target'] ?? '_blank') === '_blank')
                                                <i class="fas fa-external-link-alt ms-1 external-icon"></i>
                                            @endif
                                        </a>
                                    @else
                                        —
                                    @endif
                                </div>
                            
                            @elseif($field['type'] === 'list')
                                <div class="show-list-field">
                                    @if(is_array($field['value']) && count($field['value']) > 0)
                                        <ul class="show-list">
                                            @foreach($field['value'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif(is_string($field['value']))
                                        {{ $field['value'] }}
                                    @else
                                        —
                                    @endif
                                </div>
                            
                            @else
                                <div class="show-default-field">
                                    {{ $field['value'] ?? '—' }}
                                </div>
                            @endif
                        </div>
                        
                        @if(isset($field['help']))
                            <small class="show-field-help">
                                {{ $field['help'] }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if(isset($actions) && count($actions) > 0)
            <div class="show-actions">
                @foreach($actions as $action)
                    @if($action['type'] === 'link')
                        <a href="{{ $action['route'] }}" 
                           class="btn-modern {{ $action['class'] ?? 'btn-secondary-modern' }}">
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} me-2"></i>
                            @endif
                            <span class="d-none d-sm-inline">{{ $action['label'] }}</span>
                            <span class="d-sm-none">{{ $action['shortLabel'] ?? $action['label'] }}</span>
                        </a>
                    @elseif($action['type'] === 'button')
                        <button type="button" 
                                class="btn-modern {{ $action['class'] ?? 'btn-secondary-modern' }}"
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                @if(isset($action['data'])) 
                                    @foreach($action['data'] as $key => $value)
                                        data-{{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif>
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} me-2"></i>
                            @endif
                            <span class="d-none d-sm-inline">{{ $action['label'] }}</span>
                            <span class="d-sm-none">{{ $action['shortLabel'] ?? $action['label'] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
/* Show Component Responsive Styles */
.show-field-wrapper {
    margin-bottom: 1.5rem;
}

.show-field-label {
    color: hsl(var(--muted-foreground));
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.field-icon {
    color: hsl(var(--accent));
    font-size: 0.875rem;
}

.show-field-value {
    background-color: hsl(var(--muted) / 0.3);
    border: 1px solid hsl(var(--border));
    border-radius: var(--radius);
    padding: 0.75rem;
    min-height: 2.5rem;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: hsl(var(--foreground));
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    word-break: break-word;
}

.show-text-field,
.show-date-field,
.show-time-field,
.show-default-field {
    font-weight: 500;
}

.show-textarea-field {
    line-height: 1.6;
    white-space: pre-wrap;
    align-items: flex-start;
    min-height: auto;
    padding: 0.75rem;
}

.show-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-color: hsl(var(--muted) / 0.5);
    border: 2px dashed hsl(var(--border));
    border-radius: var(--radius);
    text-align: center;
    color: hsl(var(--muted-foreground));
}

.show-image-placeholder i {
    font-size: 2rem;
    opacity: 0.5;
    margin-bottom: 0.5rem;
}

.show-image-placeholder p {
    font-size: 0.875rem;
    margin: 0;
}

.show-link {
    color: hsl(var(--accent));
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.show-link:hover {
    color: hsl(var(--accent) / 0.8);
    text-decoration: underline;
}

.link-text {
    word-break: break-all;
}

.external-icon {
    font-size: 0.75rem;
    flex-shrink: 0;
}

.show-list {
    margin: 0;
    padding-left: 1.25rem;
    list-style-type: disc;
}

.show-list li {
    margin-bottom: 0.25rem;
    color: hsl(var(--foreground));
}

.date-value {
    font-weight: 500;
    color: hsl(var(--foreground));
}

.text-muted {
    color: hsl(var(--muted-foreground)) !important;
    font-size: 0.75rem;
}

.show-field-help {
    color: hsl(var(--muted-foreground));
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
    line-height: 1.4;
}

.show-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    margin-top: 1rem;
    border-top: 1px solid hsl(var(--border));
    flex-wrap: wrap;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .show-field-wrapper {
        margin-bottom: 1.25rem;
    }
    
    .show-field-value {
        padding: 0.625rem;
        font-size: 0.8rem;
        min-height: 2rem;
    }
    
    .show-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .show-actions .btn-modern {
        width: 100%;
        justify-content: center;
    }
    
    .show-image {
        max-width: 100% !important;
    }
    
    .show-image-placeholder {
        padding: 1.5rem;
    }
    
    .show-image-placeholder i {
        font-size: 1.5rem;
    }
    
    .show-link {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .external-icon {
        align-self: flex-start;
    }
}

@media (max-width: 576px) {
    .card-body-modern {
        padding: 1rem;
    }
    
    .show-field-wrapper {
        margin-bottom: 1rem;
    }
    
    .show-field-label {
        font-size: 0.7rem;
        margin-bottom: 0.375rem;
    }
    
    .show-field-value {
        padding: 0.5rem;
        font-size: 0.75rem;
        min-height: 1.75rem;
    }
    
    .show-actions {
        padding-top: 1rem;
    }
    
    .btn-modern {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
    
    .field-icon {
        font-size: 0.75rem;
    }
    
    .badge-modern {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
    }
}

/* Bootstrap grid responsive adjustments */
.row.g-3 {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

.row.g-md-4 {
    --bs-gutter-y: 1.5rem;
}

@media (max-width: 576px) {
    .row.g-3 {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }
    
    .row.g-md-4 {
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

/* Card header responsive */
@media (max-width: 768px) {
    .card-header-modern {
        padding: 1rem;
    }
    
    .card-header-modern .btn-modern {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
}
</style>
