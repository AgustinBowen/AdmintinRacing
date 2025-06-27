<div class="col-md-{{ $width ?? 3 }}">
    <div class="card-modern h-100" 
         style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;"
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-lg)';"
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)';">
        <div class="card-body-modern">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline mb-1">
                        <h2 class="mb-0 fw-bold" 
                            style="color: hsl(var(--foreground)); font-size: 2rem; line-height: 1;">
                            {{ $value }}
                        </h2>
                    </div>
                    <p class="mb-0 fw-medium" 
                       style="color: hsl(var(--muted-foreground)); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $label }}
                    </p>
                </div>
                <div class="ms-3 d-flex align-items-center justify-content-center" 
                     style="width: 3.5rem; height: 3.5rem; border-radius: var(--radius); background-color: hsl(var(--muted) / 0.5);">
                    @php
                        $iconColors = [
                            'primary' => 'hsl(var(--accent))',
                            'success' => 'hsl(var(--success))',
                            'danger' => 'hsl(var(--accent))',
                            'warning' => 'hsl(var(--warning))',
                            'info' => '#3b82f6',
                            'secondary' => 'hsl(var(--muted-foreground))'
                        ];
                        $iconColor = $iconColors[$color ?? 'primary'] ?? 'hsl(var(--accent))';
                    @endphp
                    <i class="fas fa-{{ $icon }}" 
                       style="font-size: 1.5rem; color: {{ $iconColor }};"></i>
                </div>
            </div>
            @if(isset($change))
                <div class="mt-3 pt-3" style="border-top: 1px solid hsl(var(--border));">
                    <div class="d-flex align-items-center">
                        <span class="badge-modern {{ $change > 0 ? 'badge-success' : 'badge-destructive' }} me-2">
                            <i class="fas fa-{{ $change > 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                            {{ abs($change) }}%
                        </span>
                        <small style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;">
                            vs mes anterior
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
