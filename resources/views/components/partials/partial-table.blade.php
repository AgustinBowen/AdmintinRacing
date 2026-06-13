<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
                @if($showActions ?? true)
                    <th>Acciones</th>
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
                                    <span class="badge">{{ data_get($item, $column['field']) }}</span>
                                    @break

                                @case('date')
                                    @php
                                        $rawDate = data_get($item, $column['field']);
                                        $date = $rawDate ? \Illuminate\Support\Carbon::parse($rawDate) : null;
                                    @endphp
                                    <span style="color: var(--white);">
                                        {{ $date ? $date->format($column['format'] ?? 'd/m/Y') : '—' }}
                                    </span>
                                    @break

                                @case('boolean')
                                    <span class="badge">
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
                            <div class="row-actions">
                                @if(isset($rowActions))
                                    @foreach($rowActions as $action)
                                        <a href="{{ route($action['route'], $item) }}" class="icon-btn" title="{{ $action['title'] ?? '' }}">
                                            <x-dynamic-component :component="'heroicon-o-' . ($action['icon'] ?? 'link')" style="width:1em; height:1em; vertical-align:-0.125em;" />
                                        </a>
                                    @endforeach
                                @endif

                                @if($showView ?? true)
                                    <a href="{{ route($routePrefix . '.show', $item) }}" class="icon-btn" title="Ver">
                                        Ver
                                    </a>
                                @endif

                                @if($showEdit ?? true)
                                    <a href="{{ route($routePrefix . '.edit', $item) }}" class="icon-btn" title="Editar">
                                        Editar
                                    </a>
                                @endif

                                @if($showDelete ?? true)
                                    <form method="POST" action="{{ route($routePrefix . '.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn del" title="Eliminar">Borrar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(method_exists($items, 'links'))
        <div style="padding:14px;">
            {{ $items->links() }}
        </div>
    @endif
</div>
