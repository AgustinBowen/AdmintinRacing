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
                            @switch($column['type'] ?? 'text')
                                @case('badge')
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
                                    @break

                                @case('date')
                                    @php
                                        $rawDate = data_get($item, $column['field']);
                                        $date = $rawDate ? \Illuminate\Support\Carbon::parse($rawDate) : null;
                                    @endphp
                                    <span style="color: hsl(var(--primary));">
                                        {{ $date ? $date->format($column['format'] ?? 'd/m/Y') : '—' }}
                                    </span>
                                    @break

                                @case('boolean')
                                    <span class="badge-modern {{ data_get($item, $column['field']) ? 'badge-success' : 'badge-destructive' }}">
                                        {{ data_get($item, $column['field']) ? 'Sí' : 'No' }}
                                    </span>
                                    @break

                                @default
                                    {{ data_get($item, $column['field']) }}
                            @endswitch
                        </td>
                    @endforeach

                    @if($showActions ?? true)
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                @if($showView ?? true)
                                    <a href="{{ route($routePrefix . '.show', $item) }}"
                                       class="btn-modern btn-secondary-modern p-2"
                                       style="width: 32px; height: 32px;"
                                       title="Ver">
                                        <i class="fas fa-eye" style="font-size: 0.75rem;"></i>
                                    </a>
                                @endif

                                @if($showEdit ?? true)
                                    <a href="{{ route($routePrefix . '.edit', $item) }}"
                                       class="btn-modern btn-secondary-modern p-2"
                                       style="width: 32px; height: 32px;"
                                       title="Editar">
                                        <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                                    </a>
                                @endif

                                @if($showDelete ?? true)
                                    <button type="button"
                                            class="btn-modern btn-destructive-modern p-2"
                                            style="width: 32px; height: 32px;"
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

    @if(method_exists($items, 'links'))
        <div class="px-4 py-3">
            {{ $items->links() }}
        </div>
    @endif
</div>
