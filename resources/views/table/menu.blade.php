@extends('layouts.table')

@section('title', 'Menu')

@section('content')
    <x-flash />

    @if (! empty($cart))
        <section class="mb-8 rounded-2xl bg-white p-5 ring-1 ring-amber-100 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-amber-950 mb-4">Your order</h2>
            <ul class="space-y-3 mb-4">
                @foreach ($cart as $line)
                    <li class="flex items-center justify-between gap-3 text-sm">
                        <span>
                            @if ($line['emoji'])<span class="mr-1">{{ $line['emoji'] }}</span>@endif
                            {{ $line['name'] }} × {{ $line['quantity'] }}
                        </span>
                        <span class="font-medium">${{ number_format($line['unit_price'] * $line['quantity'], 2) }}</span>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('table.cart.update') }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="menu_item_id" value="{{ $line['menu_item_id'] }}">
                            <label class="sr-only">Quantity</label>
                            <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99"
                                class="w-16 rounded-lg border border-amber-200 px-2 py-1 text-center text-sm">
                            <button type="submit" class="text-xs text-amber-800 font-medium">Update</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="flex items-center justify-between border-t border-amber-100 pt-4 mb-4">
                <span class="font-semibold text-amber-950">Total</span>
                <span class="font-display text-xl font-semibold text-amber-950">${{ number_format($cartTotal, 2) }}</span>
            </div>
            <form method="POST" action="{{ route('table.order') }}">
                @csrf
                <button type="submit" class="w-full rounded-full bg-amber-800 py-3.5 text-sm font-semibold text-amber-50 hover:bg-amber-900">
                    Send order to counter
                </button>
            </form>
        </section>
    @endif

    @forelse ($itemsByCategory as $category => $items)
        <section class="mb-10">
            <h2 class="font-display text-xl font-semibold text-amber-950 capitalize mb-4">{{ $category }}</h2>
            <div class="space-y-3">
                @foreach ($items as $item)
                    <article class="flex items-center gap-4 rounded-2xl bg-white p-4 ring-1 ring-amber-100 shadow-sm">
                        <span class="text-2xl shrink-0" aria-hidden="true">{{ $item->emoji ?? '🍽️' }}</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-amber-950">{{ $item->name }}</h3>
                            @if ($item->description)
                                <p class="text-sm text-stone-500 mt-0.5">{{ $item->description }}</p>
                            @endif
                            <p class="text-sm font-semibold text-amber-800 mt-1">{{ $item->formattedPrice() }}</p>
                        </div>
                        <form method="POST" action="{{ route('table.cart.add', $item) }}">
                            @csrf
                            <button type="submit" class="shrink-0 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-200">
                                Add
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="text-center text-stone-500 py-12">Menu is being updated — check back soon!</p>
    @endforelse

    <form method="POST" action="{{ route('table.leave') }}" class="mt-8 text-center">
        @csrf
        <button type="submit" class="text-xs text-stone-400 hover:text-stone-600">Leave this table</button>
    </form>
@endsection
