@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </span>
        </div>
        
        <div class="d-flex align-items-center gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="btn-modern btn-secondary-modern" style="opacity: 0.5; cursor: not-allowed; padding: 0.5rem 0.75rem;">
                    <i class="fas fa-chevron-left" style="font-size: 0.75rem;"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-modern btn-secondary-modern" style="padding: 0.5rem 0.75rem;" title="Página anterior">
                    <i class="fas fa-chevron-left" style="font-size: 0.75rem;"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="btn-modern btn-secondary-modern" style="opacity: 0.5; cursor: default; padding: 0.5rem 0.75rem;">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-modern btn-primary-modern" style="padding: 0.5rem 0.75rem; font-weight: 600;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="btn-modern btn-secondary-modern" style="padding: 0.5rem 0.75rem;" title="Página {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-modern btn-secondary-modern" style="padding: 0.5rem 0.75rem;" title="Página siguiente">
                    <i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i>
                </a>
            @else
                <span class="btn-modern btn-secondary-modern" style="opacity: 0.5; cursor: not-allowed; padding: 0.5rem 0.75rem;">
                    <i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i>
                </span>
            @endif
        </div>
    </nav>
@endif