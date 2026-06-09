@if ($paginator->hasPages())
    <nav role="navigation" style="margin-top: 1rem; padding: 1rem 0; border-top: 1px solid var(--line);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <p class="text-sm" style="margin: 0; color: var(--bone); font-family: var(--font-sans);">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </p>
            
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                @else
                    <li class="page-item"><a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev"><i class="fas fa-chevron-left"></i></a></li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a href="{{ $url }}" class="page-link">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item"><a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next"><i class="fas fa-chevron-right"></i></a></li>
                @else
                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
                @endif
            </ul>
        </div>
    </nav>
@endif