@extends('layouts.staff')

@section('title', 'Kitchen Dashboard')

@section('content')
    <div class="p-4">
        <!-- Kitchen Display System (KDS) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Column 1: To Do (Incoming Orders) -->
            <div class="bg-slate-100 rounded-lg p-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                    To Do
                    <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">{{ $todoOrders->count() }}</span>
                </h2>
                <div id="todo-column" class="space-y-3 min-h-[400px]">
                    @foreach($todoOrders as $order)
                        @include('partials.order-card', ['order' => $order, 'state' => 'todo'])
                    @endforeach
                    @if($todoOrders->isEmpty())
                        <p class="text-slate-500 text-center py-8">No orders to do</p>
                    @endif
                </div>
            </div>

            <!-- Column 2: In Progress (Cooking) -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    In Progress
                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">{{ $inProgressOrders->count() }}</span>
                </h2>
                <div id="in-progress-column" class="space-y-3 min-h-[400px]">
                    @foreach($inProgressOrders as $order)
                        @include('partials.order-card', ['order' => $order, 'state' => 'in-progress'])
                    @endforeach
                    @if($inProgressOrders->isEmpty())
                        <p class="text-slate-500 text-center py-8">No orders in progress</p>
                    @endif
                </div>
            </div>

            <!-- Column 3: Ready (Done) -->
            <div class="bg-emerald-50 rounded-lg p-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                    Ready
                    <span class="bg-emerald-500 text-white text-xs px-2 py-1 rounded-full">{{ $readyOrders->count() }}</span>
                </h2>
                <div id="ready-column" class="space-y-3 min-h-[400px]">
                    @foreach($readyOrders as $order)
                        @include('partials.order-card', ['order' => $order, 'state' => 'ready'])
                    @endforeach
                    @if($readyOrders->isEmpty())
                        <p class="text-slate-500 text-center py-8">No orders ready</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Move order to different state (for future AJAX implementation)
        function moveOrder(orderId, newState) {
            // This will be implemented with AJAX to update item readiness
            console.log('Move order', orderId, 'to', newState);
            // For now, just reload the page to see changes
            location.reload();
        }
    </script>
@endsection
