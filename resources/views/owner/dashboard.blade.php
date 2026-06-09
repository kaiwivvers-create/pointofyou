@extends('layouts.staff')

@section('title', 'Owner Dashboard')

@section('content')
    <div class="p-4">
        <!-- Top Bar: Universal Attendance Component -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Punch Card Button -->
                @if ($permit && $permit->status === 'approved')
                    <button disabled class="bg-slate-300 text-slate-500 px-6 py-3 rounded-lg font-semibold text-lg cursor-not-allowed">
                        🟢 Permitted ({{ ucfirst($permit->type) }})
                    </button>
                @elseif (!$attendance || !$attendance->check_in)
                    <button onclick="checkIn()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold text-lg">
                        ▶ Start Shift (Check In)
                    </button>
                @else
                    <div class="flex items-center gap-4">
                        <button onclick="checkOut()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold text-lg">
                            ⏹ End Shift (Check Out)
                        </button>
                        <div class="text-slate-700">
                            <span class="text-sm">Hours Worked:</span>
                            <span id="hours-worked" class="font-semibold">{{ $attendance->check_in ? $attendance->check_in->diff(now())->format('%h:%i:%s') : '0:00:00' }}</span>
                        </div>
                    </div>
                @endif

                <!-- Permission Badge -->
                @if ($permit)
                    @if ($permit->status === 'approved')
                        <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-medium">
                            🟢 Status: Permitted ({{ ucfirst($permit->type) }})
                        </span>
                    @elseif ($permit->status === 'pending')
                        <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full text-sm font-medium">
                            🟡 Status: Permit Pending
                        </span>
                    @endif
                @endif
            </div>

            <div class="text-right">
                <p class="text-2xl font-bold text-slate-900">{{ now()->format('H:i') }}</p>
                <p class="text-sm text-slate-500">{{ now()->format('l, F j') }}</p>
            </div>
        </div>

        <!-- Main ERP Layout with Sidebar -->
        <div class="flex gap-4">
            <!-- Sidebar -->
            <div class="w-64 bg-white rounded-lg shadow-sm p-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4">ERP Dashboard</h2>
                <div class="space-y-2">
                    <button onclick="switchTab('staff')" id="tab-staff" class="w-full text-left px-4 py-3 rounded-lg font-medium bg-emerald-600 text-white">
                        Staff Management
                    </button>
                    <button onclick="switchTab('holidays')" id="tab-holidays" class="w-full text-left px-4 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Holidays & Day Offs
                    </button>
                    <button onclick="switchTab('analytics')" id="tab-analytics" class="w-full text-left px-4 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Analytics & Reports
                    </button>
                    <button onclick="switchTab('inventory')" id="tab-inventory" class="w-full text-left px-4 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Inventory Control
                    </button>
                    <button onclick="switchTab('menu')" id="tab-menu" class="w-full text-left px-4 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Menu Management
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Tab A: Staff Management -->
                <div id="content-staff" class="tab-content">
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Pending Requests</h2>
                        @if ($pendingPermits->count() > 0)
                            <div class="space-y-3">
                                @foreach ($pendingPermits as $permit)
                                    <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $permit->user ? $permit->user->name : '-' }}</p>
                                            <p class="text-sm text-slate-600">Date: {{ $permit->start_date->format('M d, Y') }}</p>
                                            <p class="text-sm text-slate-600">Reason: {{ $permit->reason }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('permits.approve', $permit) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">Approve</button>
                                            </form>
                                            <button onclick="openRejectModal({{ $permit->id }})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">Deny</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">No pending requests.</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Active Shifts Today</h2>
                        @if ($activeShifts->count() > 0)
                            <div class="space-y-3">
                                @foreach ($activeShifts as $shift)
                                    <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $shift->user ? $shift->user->name : '-' }}</p>
                                            <p class="text-sm text-slate-600">Clocked in since {{ $shift->check_in->format('H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-slate-700">Hours worked: {{ $shift->check_in->diffInMinutes(now()) / 60 }}h</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">No active shifts today.</p>
                        @endif
                    </div>
                </div>

                <!-- Tab B: Analytics & Reports -->
                <div id="content-analytics" class="tab-content hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <p class="text-sm text-slate-500 mb-1">Total Revenue Today</p>
                            <p class="text-3xl font-bold text-emerald-600">${{ number_format($todayIncome, 2) }}</p>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <p class="text-sm text-slate-500 mb-1">Total Net Profit</p>
                            <p class="text-3xl font-bold text-slate-900">${{ number_format($thisMonthIncome - 0, 2) }}</p>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <p class="text-sm text-slate-500 mb-1">Average Order Value</p>
                            <p class="text-3xl font-bold text-slate-900">${{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00' }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Sales Graph</h2>
                        <div class="h-80">
                            <canvas id="incomeChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Low Stock Alerts</h2>
                        @if ($lowStockProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach ($lowStockProducts as $product)
                                    <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                            <p class="text-sm text-slate-600">Remaining: {{ $product->stock_quantity }}</p>
                                        </div>
                                        <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-medium">Low Stock</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">No low stock alerts.</p>
                        @endif
                    </div>
                </div>

                <!-- Tab C: Holidays & Day Offs -->
                <div id="content-holidays" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-slate-900">Holidays & Day Offs</h2>
                            <a href="{{ route('holidays.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">Add New</a>
                        </div>
                        
                        @php
                            $holidays = \App\Models\Holiday::orderBy('date')->get();
                        @endphp
                        
                        @if ($holidays->count() > 0)
                            <div class="space-y-3">
                                @foreach ($holidays as $holiday)
                                    <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $holiday->name }}</p>
                                            <p class="text-sm text-slate-600">{{ $holiday->date->format('M d, Y') }}</p>
                                            <div class="flex gap-2 mt-1">
                                                @if ($holiday->type === 'holiday')
                                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Holiday</span>
                                                @else
                                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">Day Off</span>
                                                @endif
                                                @if ($holiday->is_recurring)
                                                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded">Recurring</span>
                                                @endif
                                            </div>
                                            @if ($holiday->notes)
                                                <p class="text-xs text-slate-500 mt-1">{{ $holiday->notes }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">Delete</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">No holidays or day offs scheduled.</p>
                        @endif
                    </div>
                </div>

                <!-- Tab D: Inventory Control -->
                <div id="content-inventory" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Inventory Control</h2>
                        <p class="text-slate-600 mb-6">Manually manage stock levels and track supplier costs.</p>
                        
                        <div class="mb-6">
                            <h3 class="font-semibold text-slate-900 mb-3">Add Stock</h3>
                            <form method="POST" action="{{ route('inventory.stock-movements') }}" class="flex gap-4">
                                @csrf
                                <select name="product_id" class="staff-input flex-1">
                                    <option value="">Select Product</option>
                                    @foreach (\App\Models\Product::all() as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantity" placeholder="Quantity" class="staff-input w-32">
                                <input type="number" name="unit_cost" placeholder="Cost per unit" step="0.01" class="staff-input w-40">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium">Add Stock</button>
                            </form>
                        </div>

                        <div>
                            <h3 class="font-semibold text-slate-900 mb-3">Stock Movements</h3>
                            <a href="{{ route('inventory.stock-movements') }}" class="text-emerald-600 hover:text-emerald-700">View Stock Movements →</a>
                        </div>
                    </div>
                </div>

                <!-- Tab D: Menu Management -->
                <div id="content-menu" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Menu Management</h2>
                        <p class="text-slate-600 mb-6">Add, edit, or remove menu items.</p>
                        
                        <a href="{{ route('admin.menu.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium inline-block">
                            Manage Menu Items
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999]">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Reject Permit</h2>
            <form method="POST" action="" id="rejectForm">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="mb-4">
                    <label for="rejection_reason" class="staff-label">Rejection Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required class="staff-input"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary bg-red-600 hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(btn => {
                btn.classList.remove('bg-emerald-600', 'text-white');
                btn.classList.add('bg-slate-100', 'text-slate-700');
            });
            
            // Show selected content
            document.getElementById(`content-${tab}`).classList.remove('hidden');
            
            // Add active state to selected tab
            document.getElementById(`tab-${tab}`).classList.remove('bg-slate-100', 'text-slate-700');
            document.getElementById(`tab-${tab}`).classList.add('bg-emerald-600', 'text-white');
        }

        // Reject modal
        function openRejectModal(permitId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = '{{ route('permits.reject', ':id') }}'.replace(':id', permitId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Check-in/Check-out functions
        async function checkIn() {
            try {
                const response = await fetch('{{ route('attendance.check-in') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                
                const data = await response.json();
                console.log('Check-in response:', data);
                
                if (response.ok) {
                    // Always reload on success, regardless of success flag
                    location.reload();
                } else {
                    alert(data.message || 'Error checking in. Please try again.');
                }
            } catch (error) {
                console.error('Error checking in:', error);
                alert('Error checking in. Please try again.');
            }
        }

        async function checkOut() {
            try {
                const response = await fetch('{{ route('attendance.check-out') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                
                const data = await response.json();
                console.log('Check-out response:', data);
                
                if (response.ok) {
                    // Always reload on success, regardless of success flag
                    location.reload();
                } else {
                    alert(data.message || 'Error checking out. Please try again.');
                }
            } catch (error) {
                console.error('Error checking out:', error);
                alert('Error checking out. Please try again.');
            }
        }

        // Update time display
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
            const dateStr = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            
            document.querySelector('.text-right p:first-child').textContent = timeStr;
            document.querySelector('.text-right p:last-child').textContent = dateStr;

            // Update hours worked if checked in
            @if ($attendance && $attendance->check_in)
                const checkInTime = new Date('{{ $attendance->check_in->format('Y-m-d H:i:s') }}');
                const diffMs = now - checkInTime;
                const hours = Math.floor(diffMs / (1000 * 60 * 60));
                const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diffMs % (1000 * 60)) / 1000);
                document.getElementById('hours-worked').textContent = `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            @endif
        }

        // Initialize chart
        function initChart() {
            const ctx = document.getElementById('incomeChart');
            if (!ctx) return;
            
            const chartData = {{ json_encode($monthlyChartData) }};
            
            const config = {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Monthly Income',
                        data: chartData.data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    }
                }
            };
            
            new Chart(ctx, config);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateTime();
            setInterval(updateTime, 1000);
            initChart();
        });
    </script>
@endsection
