@php
    $createdDate = new \Carbon\Carbon($order->created_at);
    $waitingTime = \Carbon\Carbon::now()->diffInMinutes($createdDate);
    $isUrgent = $waitingTime > 10;
    $bgColor = $isUrgent ? 'bg-orange-100 border-orange-300' : 'bg-white border-slate-200';
@endphp

<div id="order-{{ $order->id }}" class="{{ $bgColor }} border rounded-lg p-4 shadow-sm transition-all">
    <div class="flex justify-between items-start mb-3">
        <div>
            <p class="font-bold text-slate-900">Order #{{ $order->id }} ({{ $order->cafeTable ? 'Dine-In' : 'Takeout' }})</p>
            @if($order->cafeTable)
                <p class="text-sm text-slate-600">Table: {{ $order->cafeTable->name }}</p>
            @endif
            <p class="text-xs text-slate-500">Waiting {{ $waitingTime }} mins</p>
        </div>
        @if($isUrgent)
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">Urgent</span>
        @endif
    </div>
    <div class="mb-3">
        @foreach($order->items as $item)
            <div class="flex justify-between items-center text-sm py-1 border-b border-slate-100 last:border-0">
                <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                @if($item->notes)
                    <span class="text-xs text-slate-500">{{ $item->notes }}</span>
                @endif
            </div>
        @endforeach
    </div>
    @if($state === 'todo')
        <button onclick="moveOrder({{ $order->id }}, 'in-progress')" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">Start Cooking</button>
    @elseif($state === 'in-progress')
        <button onclick="moveOrder({{ $order->id }}, 'ready')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-medium">Mark as Ready</button>
    @endif
</div>
