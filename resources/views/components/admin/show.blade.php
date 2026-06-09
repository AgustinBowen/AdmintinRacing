<div class="view-head">
    <h1>{{ strtoupper($title) }}</h1>
</div>

<div class="form-card" style="width: 100%; max-width: none; margin-top: 24px; padding: 32px;">
    <div class="fgrid" style="row-gap: 24px;">
        @foreach($fields as $field)
        <div class="field {{ ($field['width'] ?? 12) == 12 ? 'full' : '' }}">
            <label style="color: var(--gray); font-size: 13px;">
                @if(isset($field['icon'])) <i class="{{ $field['icon'] }} me-1" style="color: var(--gray);"></i> @endif
                {{ strtoupper($field['label']) }}
            </label>
            <div style="font-size: 18px; font-weight: 500; color: var(--bone); border-bottom: 1px solid var(--carbon); padding-bottom: 8px;">
                @if($field['type'] === 'badge')
                    <span class="badge" style="background: var(--carbon); color: var(--bone); padding: 4px 10px; border-radius: 4px; border: 1px solid var(--line);">{{ $field['value'] ?? '—' }}</span>
                @else
                    {{ $field['value'] ?? '—' }}
                @endif
            </div>
            @if(isset($field['help']))
                <small style="color: var(--gray); font-size: 12px; margin-top: 4px; display: block;">{{ $field['help'] }}</small>
            @endif
        </div>
        @endforeach
    </div>

    @if(isset($actions) && count($actions) > 0)
    <div class="form-actions" style="justify-content: flex-start; gap: 12px; flex-wrap: wrap; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--carbon);">
        @foreach($actions as $action)
            @if($action['type'] === 'link')
                <a href="{{ $action['route'] }}" class="btn {{ str_contains($action['class'] ?? '', 'primary') ? '' : 'ghost' }}" style="{{ str_contains($action['class'] ?? '', 'primary') ? 'background: var(--white); color: var(--black);' : '' }}">
                    @if(isset($action['icon'])) <i class="{{ $action['icon'] }}"></i> @endif
                    {{ $action['label'] }}
                </a>
            @elseif($action['type'] === 'button')
                <button type="button" class="btn {{ str_contains($action['class'] ?? '', 'destructive') ? 'ghost' : 'ghost' }}"
                        style="{{ str_contains($action['class'] ?? '', 'destructive') ? 'color: var(--racing); border-color: var(--racing);' : '' }}"
                        @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                        @if(isset($action['data'])) 
                            @foreach($action['data'] as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach
                        @endif>
                    @if(isset($action['icon'])) <i class="{{ $action['icon'] }}"></i> @endif
                    {{ $action['label'] }}
                </button>
            @endif
        @endforeach
    </div>
    @endif
</div>
