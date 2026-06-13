<div class="view-head">
    <h1>{{ strtoupper($title) }}</h1>
    
    @if(isset($actions) && count($actions) > 0)
    <div class="head-actions" style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
        @foreach($actions as $action)
            @if($action['type'] === 'link')
                <a href="{{ $action['route'] }}" class="btn {{ str_contains($action['class'] ?? '', 'primary') ? '' : 'ghost' }}" style="{{ str_contains($action['class'] ?? '', 'primary') ? 'background: var(--white); color: var(--black);' : 'padding: 8px 16px; font-size: 13px;' }}">
                    @if(isset($action['icon'])) <x-dynamic-component :component="'heroicon-o-' . ($action['icon'])" style="width:1em; height:1em; vertical-align:-0.125em;" /> @endif
                    {{ $action['label'] }}
                </a>
            @elseif($action['type'] === 'button')
                <button type="button" class="btn {{ str_contains($action['class'] ?? '', 'destructive') ? 'danger' : 'ghost' }}"
                        style="padding: 8px 16px; font-size: 13px;"
                        @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                        @if(isset($action['data'])) 
                            @foreach($action['data'] as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach
                        @endif>
                    @if(isset($action['icon'])) <x-dynamic-component :component="'heroicon-o-' . ($action['icon'])" style="width:1em; height:1em; vertical-align:-0.125em;" /> @endif
                    {{ $action['label'] }}
                </button>
            @endif
        @endforeach
    </div>
    @endif
</div>

<div class="info-card" style="margin-top: 24px; background: var(--carbon); border: 1px solid var(--line); padding: 32px; border-radius: 4px;">
    <div class="fgrid" style="row-gap: 32px;">
        @foreach($fields as $field)
        <div class="field {{ ($field['width'] ?? 12) == 12 ? 'full' : '' }}" style="grid-column: span {{ $field['width'] ?? 12 }};">
            <label style="color: var(--gray); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                @if(isset($field['icon'])) <x-dynamic-component :component="'heroicon-o-' . ($field['icon'])" style="width:1em; height:1em; vertical-align:-0.125em; color: var(--gray);" /> @endif
                {{ $field['label'] }}
            </label>
            <div style="font-size: 16px; font-weight: 500; color: var(--white); line-height: 1.5;">
                @if($field['type'] === 'badge')
                    <span class="badge" style="background: var(--black); color: var(--bone); padding: 6px 12px; border-radius: 4px; border: 1px solid var(--line); font-size: 14px;">{{ $field['value'] ?? '—' }}</span>
                @else
                    {{ $field['value'] ?? '—' }}
                @endif
            </div>
            @if(isset($field['help']))
                <small style="color: var(--gray); font-size: 12px; margin-top: 6px; display: block;">{{ $field['help'] }}</small>
            @endif
        </div>
        @endforeach
    </div>
</div>
