@php
    $active = $active ?? false;
@endphp

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
        'bg-amber-200 text-amber-950 shadow-sm' => $active,
        'text-amber-700 hover:bg-amber-100 hover:text-amber-950' => ! $active,
    ])
>
    @if (! empty($icon))
        <span @class([
            'flex size-8 shrink-0 items-center justify-center rounded-lg text-base transition-colors',
            'text-amber-900' => $active,
            'text-amber-600 group-hover:text-amber-800' => ! $active,
        ]) aria-hidden="true">{!! $icon !!}</span>
    @endif
    <span>{{ $label }}</span>
</a>
