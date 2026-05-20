@php
    $active = $active ?? false;
@endphp

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
        'bg-amber-800/90 text-amber-50 shadow-sm shadow-amber-950/20 ring-1 ring-amber-700/50' => $active,
        'text-amber-100/85 hover:bg-amber-900/45 hover:text-amber-50' => ! $active,
    ])
>
    @if (! empty($icon))
        <span @class([
            'flex size-8 shrink-0 items-center justify-center rounded-lg text-base transition-colors',
            'bg-amber-700/50' => $active,
            'bg-amber-900/30 group-hover:bg-amber-900/50' => ! $active,
        ]) aria-hidden="true">{{ $icon }}</span>
    @endif
    <span>{{ $label }}</span>
</a>
