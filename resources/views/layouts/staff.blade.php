@php
    $brandSettings = \App\Models\BrandSettings::getSettings();
    $favicon = $brandSettings->logo ? asset('storage/' . $brandSettings->logo) : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff') — {{ $brandSettings->app_name }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <style>
        :root {
            --primary-color: {{ $brandSettings->primary_color }};
            --primary-font-color: {{ $brandSettings->primary_font_color }};
            --secondary-color: {{ $brandSettings->secondary_color }};
            --accent-color: {{ $brandSettings->accent_color }};
        }
        .text-primary-font {
            color: var(--primary-font-color) !important;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex w-72 shrink-0 flex-col fixed top-0 left-0 h-full overflow-hidden" style="background-color: {{ $brandSettings->secondary_color }}; border-right: 1px solid {{ $brandSettings->primary_color }}40;">
            <div class="p-6 pb-4 flex-shrink-0">
                <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg p-2 -m-2 transition-colors" style="--hover-bg: {{ $brandSettings->primary_color }}18;" onmouseenter="this.style.backgroundColor=this.style.getPropertyValue('--hover-bg')" onmouseleave="this.style.backgroundColor=''">
                    @if ($brandSettings->logo)
                        <img src="{{ asset('storage/' . $brandSettings->logo) }}" alt="{{ $brandSettings->app_name }}" class="size-10 rounded-lg object-cover">
                    @else
                        <div class="flex size-10 items-center justify-center rounded-lg text-xl font-semibold" style="background-color: {{ $brandSettings->primary_color }}30; color: {{ $brandSettings->primary_font_color }};">
                            {{ $brandSettings->logo_fallback }}
                        </div>
                    @endif
                    <div>
                        <span class="font-sans text-lg font-semibold leading-tight block" style="color: {{ $brandSettings->primary_font_color }};">{{ $brandSettings->app_name }}</span>
                        <span class="text-xs" style="color: {{ $brandSettings->primary_font_color }}99;">Staff portal</span>
                    </div>
                </a>
            </div>

            <div class="mx-6 mb-5 rounded-lg px-4 py-3 shadow-sm flex-shrink-0" style="background-color: {{ $brandSettings->primary_color }}12; border: 1px solid {{ $brandSettings->primary_color }}30;">
                <p class="text-sm font-semibold truncate" style="color: {{ $brandSettings->primary_font_color }};">{{ Auth::user()->name }}</p>
                <p class="text-xs mt-0.5" style="color: {{ $brandSettings->primary_font_color }}99;">{{ Auth::user()->role->label() }}</p>
            </div>

            <div class="flex-1 px-4 pb-4 overflow-y-auto">
                @include('partials.staff-sidebar')
            </div>

            <div class="p-4 mx-2 mb-2 no-print flex-shrink-0" style="border-top: 1px solid {{ $brandSettings->primary_color }}30;">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors"
                        style="color: {{ $brandSettings->primary_font_color }};"
                        onmouseenter="this.style.backgroundColor='{{ $brandSettings->primary_color }}18'""
                        onmouseleave="this.style.backgroundColor=''">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 lg:ml-72 w-full">
            {{-- Mobile header + nav --}}
            <header class="lg:hidden border-b border-slate-200 bg-white sticky top-0 z-30">
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-sans font-semibold text-slate-900 truncate">{{ $brandSettings->app_name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->name }} · {{ Auth::user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="no-print">
                        @csrf
                        <button type="submit" class="shrink-0 text-sm font-semibold text-slate-700 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200">Sign out</button>
                    </form>
                </div>
                <div class="px-4 pb-3 overflow-x-auto">
                    @include('partials.staff-sidebar-mobile')
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 lg:p-10 w-full overflow-auto">
                @php
                    $user = auth()->user();
                    $showAttendanceBanner = $user && !$user->isManager() && !$user->isOwner() && !$user->isSuperAdmin();
                @endphp
                <!-- Attendance Check-in/Check-out Banner -->
                @if($showAttendanceBanner)
                <div id="attendance-banner" class="mb-6 p-4 rounded-lg bg-slate-100 border border-slate-200 hidden">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Attendance Tracking</p>
                            <p id="attendance-status" class="text-xs text-slate-600">Loading status...</p>
                        </div>
                        <div class="flex gap-2">
                            <button id="check-in-btn" onclick="checkIn()" class="staff-btn-primary text-sm">Check In</button>
                            <button id="check-out-btn" onclick="checkOut()" class="staff-btn-secondary text-sm hidden">Check Out</button>
                        </div>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')

    <!-- Checkout Confirmation Modal -->
    <div id="checkout-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6 transform scale-95 transition-transform duration-300" id="checkout-modal-content">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Confirm Check Out</h2>
            <p class="text-slate-600 mb-4">Are you sure you want to check out? This action cannot be undone.</p>
            
            <div class="mb-4">
                <p id="countdown-text" class="text-sm text-slate-500 mb-2">Please wait <span id="countdown" class="font-bold text-red-600">5</span> seconds before confirming...</p>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div id="countdown-bar" class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 100%"></div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeCheckoutModal()" class="flex-1 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Cancel</button>
                <button id="confirm-checkout-btn" onclick="confirmCheckout()" disabled class="flex-1 py-3 rounded-lg font-medium text-white disabled:cursor-not-allowed transition-all duration-300" style="background-color: #cbd5e1; color: #64748b;">Confirm Check Out</button>
            </div>
        </div>
    </div>

    <script>
        // Store routes as JavaScript variables
        const attendanceCheckInRoute = "{{ route('attendance.check-in') }}";
        const attendanceCheckOutRoute = "{{ route('attendance.check-out') }}";
        const attendanceStatusRoute = "{{ route('attendance.status') }}";

        // Attendance Check-in/Check-out
        document.addEventListener('DOMContentLoaded', function() {
            loadAttendanceStatus();
        });

        async function loadAttendanceStatus() {
            try {
                const response = await fetch(attendanceStatusRoute);
                const data = await response.json();
                
                console.log('Attendance status data:', data);
                
                const banner = document.getElementById('attendance-banner');
                const statusText = document.getElementById('attendance-status');
                const checkInBtn = document.getElementById('check-in-btn');
                const checkOutBtn = document.getElementById('check-out-btn');

                if (!banner) {
                    console.error('Attendance banner not found');
                    return;
                }

                if (data.status === 'no_employee') {
                    banner.classList.add('hidden');
                    return;
                }

                banner.classList.remove('hidden');

                if (data.status === 'not_checked_in') {
                    statusText.textContent = 'Not checked in today';
                    checkInBtn.classList.remove('hidden');
                    checkOutBtn.classList.add('hidden');
                } else if (data.status === 'checked_in') {
                    statusText.textContent = `Checked in at ${data.check_in_time}`;
                    checkInBtn.classList.add('hidden');
                    checkOutBtn.classList.remove('hidden');
                } else if (data.status === 'checked_out') {
                    statusText.textContent = `Checked out at ${data.check_out_time} (${data.hours_worked} hours)`;
                    checkInBtn.classList.add('hidden');
                    checkOutBtn.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error loading attendance status:', error);
            }
        }

        async function checkIn() {
            try {
                const response = await fetch(attendanceCheckInRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                
                if (response.ok) {
                    loadAttendanceStatus();
                    // Show success message
                    const banner = document.getElementById('attendance-banner');
                    banner.classList.add('bg-emerald-50', 'border-emerald-200');
                    setTimeout(() => {
                        banner.classList.remove('bg-emerald-50', 'border-emerald-200');
                    }, 3000);
                } else {
                    alert('Error checking in. Please try again.');
                }
            } catch (error) {
                console.error('Error checking in:', error);
                alert('Error checking in. Please try again.');
            }
        }

        let countdownInterval = null;
        let countdownValue = 5;

        function checkOut() {
            // Check if modal exists
            const modal = document.getElementById('checkout-modal');
            if (!modal) {
                console.error('Checkout modal not found');
                return;
            }
            
            // Clear any existing countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            // Show modal with animation
            const modalContent = document.getElementById('checkout-modal-content');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Trigger animation
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
            
            // Reset countdown
            countdownValue = 5;
            const countdownEl = document.getElementById('countdown');
            const countdownTextEl = document.getElementById('countdown-text');
            const countdownBarEl = document.getElementById('countdown-bar');
            const confirmBtnEl = document.getElementById('confirm-checkout-btn');
            
            console.log('Countdown elements found:', {
                countdownEl: !!countdownEl,
                countdownTextEl: !!countdownTextEl,
                countdownBarEl: !!countdownBarEl,
                confirmBtnEl: !!confirmBtnEl
            });
            
            if (countdownEl) countdownEl.textContent = countdownValue;
            if (countdownTextEl) countdownTextEl.textContent = 'Please wait ' + countdownValue + ' seconds before confirming...';
            if (countdownBarEl) countdownBarEl.style.width = '100%';
            if (confirmBtnEl) {
                confirmBtnEl.disabled = true;
                confirmBtnEl.style.backgroundColor = '#cbd5e1';
                confirmBtnEl.style.color = '#64748b';
            }
            
            // Start countdown
            countdownInterval = setInterval(() => {
                countdownValue--;
                if (countdownEl) countdownEl.textContent = countdownValue;
                if (countdownBarEl) countdownBarEl.style.width = `${(countdownValue / 5) * 100}%`;
                
                if (countdownValue <= 0) {
                    clearInterval(countdownInterval);
                    if (confirmBtnEl) {
                        confirmBtnEl.disabled = false;
                        confirmBtnEl.style.backgroundColor = '#dc2626';
                        confirmBtnEl.style.color = '#ffffff';
                    }
                    if (countdownTextEl) countdownTextEl.textContent = 'You can now confirm your check out';
                }
            }, 1000);
        }

        function closeCheckoutModal() {
            const modal = document.getElementById('checkout-modal');
            if (!modal) return;
            
            const modalContent = document.getElementById('checkout-modal-content');
            
            // Animate out
            modal.classList.add('opacity-0');
            if (modalContent) {
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
            }
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
            
            // Clear countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        async function confirmCheckout() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    alert('Error: CSRF token not found');
                    return;
                }
                
                const response = await fetch(attendanceCheckOutRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    closeCheckoutModal();
                    loadAttendanceStatus();
                    // Show success message
                    const banner = document.getElementById('attendance-banner');
                    if (banner) {
                        banner.classList.add('bg-emerald-50', 'border-emerald-200');
                        setTimeout(() => {
                            banner.classList.remove('bg-emerald-50', 'border-emerald-200');
                        }, 3000);
                    }
                } else {
                    alert(data.message || 'Error checking out. Please try again.');
                }
            } catch (error) {
                console.error('Error checking out:', error);
                alert('Error checking out. Please try again.');
            }
        }
    </script>
</body>
</html>
