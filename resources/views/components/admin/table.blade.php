<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
        @if(isset($createRoute) && $items->count() >= 1)
            <a href="{{ $createRoute }}" class="btn-modern btn-primary-modern">
                <i class="fas fa-plus me-1"></i> {{ $createText ?? 'Crear' }}
            </a>
        @endif
    </div>
    <div class="p-0">
        @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                            @if($showActions ?? true)
                                <th width="150" class="text-center">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                @foreach($columns as $column)
                                    <td>
                                        @if($column['type'] === 'badge')
                                            @php
                                                $badgeClass = match($column['color'] ?? 'primary') {
                                                    'success' => 'badge-success',
                                                    'danger' => 'badge-destructive',
                                                    'warning' => 'badge-warning',
                                                    'secondary' => 'badge-secondary',
                                                    default => 'badge-primary'
                                                };
                                            @endphp
                                            <span class="badge-modern {{ $badgeClass }}">
                                                {{ data_get($item, $column['field']) }}
                                            </span>
                                        @elseif($column['type'] === 'date')
                                            @php
                                                $rawDate = data_get($item, $column['field']);
                                                $date = $rawDate ? \Illuminate\Support\Carbon::parse($rawDate) : null;
                                            @endphp
                                            <span style="color: hsl(var(--primary));">
                                                {{ $date ? $date->format($column['format'] ?? 'd/m/Y') : '—' }}
                                            </span>
                                        @elseif($column['type'] === 'boolean')
                                            <span class="badge-modern {{ data_get($item, $column['field']) ? 'badge-success' : 'badge-destructive' }}">
                                                {{ data_get($item, $column['field']) ? 'Sí' : 'No' }}
                                            </span>
                                        @else
                                            {{ data_get($item, $column['field']) }}
                                        @endif
                                    </td>
                                @endforeach
                                
                                @if($showActions ?? true)
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            @if($showView ?? true)
                                                <a href="{{ route($routePrefix . '.show', $item) }}" 
                                                   class="btn-modern btn-secondary-modern p-2"
                                                   style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                                   title="Ver">
                                                    <i class="fas fa-eye" style="font-size: 0.75rem;"></i>
                                                </a>
                                            @endif
                                            @if($showEdit ?? true)
                                                <a href="{{ route($routePrefix . '.edit', $item) }}" 
                                                   class="btn-modern btn-secondary-modern p-2"
                                                   style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                                   title="Editar">
                                                    <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                                                </a>
                                            @endif
                                            @if($showDelete ?? true)
                                                <button type="button" 
                                                        class="btn-modern btn-destructive-modern p-2"
                                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                                        title="Eliminar"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#{{ $deleteModalId ?? 'deleteModal' }}"
                                                        data-delete-url="{{ route($routePrefix . '.destroy', $item) }}"
                                                        data-item-name="{{ data_get($item, $nameField ?? 'name') ?? 'este elemento' }}">
                                                    <i class="fas fa-trash" style="font-size: 0.75rem;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
            </div>
            
            @if(method_exists($items, 'links'))
                <div class="px-4 py-3">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
                </div>
                <h6 class="mb-2" style="color: hsl(var(--foreground));">No hay datos</h6>
                <p class="mb-3" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                    {{ $emptyMessage ?? 'No hay elementos para mostrar' }}
                </p>
                @if(isset($createRoute))
                    <a href="{{ $createRoute }}" class="btn-modern btn-primary-modern">
                        <i class="fas fa-plus me-1"></i> {{ $createText ?? 'Crear el primero' }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Incluir el modal de eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => $deleteModalId ?? 'deleteModal',
    'title' => $deleteModalTitle ?? 'Confirmar Eliminación',
    'message' => $deleteModalMessage ?? '¿Estás seguro de que deseas eliminar',
    'warningText' => $deleteModalWarning ?? 'Esta acción no se puede deshacer.',
    'confirmText' => $deleteModalConfirmText ?? 'Eliminar',
    'cancelText' => $deleteModalCancelText ?? 'Cancelar'
])