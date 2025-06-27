<div class="d-flex justify-content-between align-items-start mb-4 pb-3">
    <div class="flex-grow-1">
        <h1 class="mb-2 fw-bold" 
            style="color: hsl(var(--foreground)); font-size: 1.875rem; line-height: 1.2; letter-spacing: -0.025em;">
            {{ $title }}
        </h1>
        @if(isset($subtitle))
            <p class="mb-0" style="color: hsl(var(--muted-foreground)); font-size: 1rem; line-height: 1.5;">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="d-flex gap-2 ms-4 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
