@php
    $active = $active ?? false;
    $brandSettings = \App\Models\BrandSettings::getSettings();
@endphp

<a
    href="{{ $href }}"
    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
    style="
        @if ($active)
            background-color: {{ $brandSettings->primary_color }}22;
            color: {{ $brandSettings->primary_font_color }};
        @else
            color: {{ $brandSettings->primary_font_color }}cc;
        @endif
    "
    onmouseenter="this.style.backgroundColor='{{ $brandSettings->primary_color }}18'; this.style.color='{{ $brandSettings->primary_font_color }}';"
    onmouseleave="this.style.backgroundColor='{{ $active ? $brandSettings->primary_color . '22' : 'transparent' }}'; this.style.color='{{ $active ? $brandSettings->primary_font_color : $brandSettings->primary_font_color . 'cc' }}';"
>
    @if (! empty($icon))
        <span class="flex size-5 shrink-0 items-center justify-center text-base transition-colors"
            style="color: {{ $active ? $brandSettings->primary_font_color : $brandSettings->primary_font_color . 'aa' }};"
        >{!! $icon !!}</span>
    @endif
    <span>{{ $label }}</span>
</a>
