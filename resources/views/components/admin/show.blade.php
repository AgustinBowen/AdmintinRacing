<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
        @if(isset($headerActions))
            <div class="d-flex gap-2">
                @foreach($headerActions as $action)
                    <a href="{{ $action['route'] }}" 
                       class="btn-modern {{ $action['class'] ?? 'btn-secondary-modern' }}"
                       title="{{ $action['title'] ?? '' }}">
                        @if(isset($action['icon']))
                            <i class="{{ $action['icon'] }} me-1"></i>
                        @endif
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card-body-modern">
        <div class="row">
            @foreach($fields as $field)
                <div class="col-md-{{ $field['width'] ?? 12 }}">
                    <div class="mb-4">
                        <label class="form-label fw-medium mb-2" 
                               style="color: hsl(var(--muted-foreground)); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }} me-1"></i>
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
                                            <small class="text-muted ms-2">
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
                                             class="show-image"
                                             style="max-width: {{ $field['maxWidth'] ?? '200px' }}; height: auto; border-radius: var(--radius); border: 1px solid hsl(var(--border));">
                                    @else
                                        <div class="show-image-placeholder">
                                            <i class="fas fa-image" style="font-size: 2rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
                                            <p style="color: hsl(var(--muted-foreground)); font-size: 0.875rem; margin: 0.5rem 0 0;">
                                                Sin imagen
                                            </p>
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
                                            {{ $field['linkText'] ?? $field['value'] }}
                                            @if(($field['target'] ?? '_blank') === '_blank')
                                                <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
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
                            <small class="form-text mt-1 d-block" 
                                   style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;">
                                {{ $field['help'] }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if(isset($actions) && count($actions) > 0)
            <div class="d-flex gap-3 pt-4 mt-2" style="border-top: 1px solid hsl(var(--border));">
                @foreach($actions as $action)
                    @if($action['type'] === 'link')
                        <a href="{{ $action['route'] }}" 
                           class="btn-modern {{ $action['class'] ?? 'btn-secondary-modern' }}">
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['label'] }}
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
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
    /* Show Component Styles */
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
    }

    .show-link {
        color: hsl(var(--accent));
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .show-link:hover {
        color: hsl(var(--accent) / 0.8);
        text-decoration: underline;
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .show-field-value {
            padding: 0.5rem;
            font-size: 0.8rem;
        }
        
        .show-image {
            max-width: 100% !important;
        }
    }
</style>