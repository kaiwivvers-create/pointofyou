@php
    $active = $active ?? false;
@endphp

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
        'bg-slate-700 text-white shadow-sm' => $active,
        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
    ])
>
    @if (! empty($icon))
        <span @class([
            'flex size-8 shrink-0 items-center justify-center rounded-lg text-base transition-colors',
            'text-white' => $active,
            'text-slate-400 group-hover:text-slate-300' => ! $active,
        ]) aria-hidden="true">{!! $icon !!}</span>
    @endif
    <span>{{ $label }}</span>
</a>
