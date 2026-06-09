<div class="view-head">
    <h1>{{ $title }} @if(isset($subtitle))<span class="lap">{{ $subtitle }}</span>@endif</h1>
    @if(isset($actions))
        <div class="head-actions">
            {{ $actions }}
        </div>
    @endif
</div>
