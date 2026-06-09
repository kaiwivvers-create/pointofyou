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
                <button onclick="openProfileModal()" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-xs font-medium transition-colors"
                    style="color: {{ $brandSettings->primary_font_color }};"
                    onmouseenter="this.style.backgroundColor='{{ $brandSettings->primary_color }}18'"
                    onmouseleave="this.style.backgroundColor=''">
                    @if (Auth::user()->employee && Auth::user()->employee->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->employee->profile_picture) }}" alt="{{ Auth::user()->name }}" class="size-8 rounded-full object-cover select-none pointer-events-none">
                    @else
                        <div class="size-8 rounded-full flex items-center justify-center text-sm font-semibold select-none pointer-events-none" style="background-color: {{ $brandSettings->primary_color }}30; color: {{ $brandSettings->primary_font_color }};">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="text-left">
                        <p class="font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs opacity-75">{{ Auth::user()->role->label() }}</p>
                    </div>
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 lg:ml-72 w-full">
            {{-- Mobile header + nav --}}
            <header class="lg:hidden flex-shrink-0 border-b border-slate-200 bg-white sticky top-0 z-30">
                <div class="px-2 py-1 flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-sans font-semibold text-slate-900 truncate text-[10px]">{{ $brandSettings->app_name }}</p>
                    </div>
                    <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="shrink-0 text-[10px] font-semibold text-slate-700 px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200">Menu</button>
                    <form method="POST" action="{{ route('admin.logout') }}" class="no-print">
                        @csrf
                        <button type="submit" class="shrink-0 text-[10px] font-semibold text-slate-700 px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200">Sign out</button>
                    </form>
                </div>
                <div id="mobile-nav" class="px-2 pb-1 overflow-x-auto hidden">
                    @include('partials.staff-sidebar-mobile')
                </div>
            </header>

            <main class="flex-1 p-1 sm:p-4 lg:p-10 w-full overflow-auto">
                @php
                    $user = auth()->user();
                    $showAttendanceBanner = $user && !$user->isManager() && !$user->isOwner() && !$user->isSuperAdmin() && $user->dbRole && $user->dbRole->is_paid;
                @endphp
                <!-- Attendance Check-in/Check-out Banner -->
                @if($showAttendanceBanner)
                <div id="attendance-banner" class="mb-2 sm:mb-6 p-2 sm:p-4 rounded-lg bg-slate-100 border border-slate-200 hidden">
                    <div class="flex items-center justify-between gap-2 sm:gap-4">
                        <div>
                            <p class="text-xs sm:text-sm font-semibold text-slate-900">Attendance Tracking</p>
                            <p id="attendance-status" class="text-[10px] sm:text-xs text-slate-600">Loading status...</p>
                        </div>
                        <div class="flex gap-1 sm:gap-2">
                            <button id="check-in-btn" onclick="checkIn()" class="staff-btn-primary text-[10px] sm:text-sm">Check In</button>
                            <button id="check-out-btn" onclick="checkOut()" class="staff-btn-secondary text-[10px] sm:text-sm hidden">Check Out</button>
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

    <!-- Face API.js for face detection -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    @include('partials.chatbot')

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

    <!-- Face Verification Modal -->
    <div id="face-verification-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6 transform scale-95 transition-transform duration-300" id="face-verification-modal-content">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Face Verification</h2>
            <p class="text-slate-600 mb-4">Please position your face in the center of the frame for verification.</p>
            
            <div class="relative mb-4">
                <video id="face-video" class="w-full rounded-lg bg-slate-900" autoplay playsinline></video>
                <canvas id="face-canvas" class="hidden"></canvas>
                <div id="face-loading" class="absolute inset-0 flex items-center justify-center bg-slate-900/50">
                    <div class="text-white text-center">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Loading camera...</p>
                    </div>
                </div>
            </div>
            
            <div id="face-status" class="mb-4 bg-slate-100 text-slate-700 px-4 py-3 rounded-lg text-center hidden">
                <span id="face-status-text">Detecting face...</span>
            </div>
            
            <div class="flex gap-3 relative z-10">
                <button onclick="closeFaceVerification()" class="flex-1 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">Cancel</button>
                <button id="capture-face-btn" onclick="captureFace()" disabled class="flex-1 py-3 rounded-lg font-medium text-white disabled:cursor-not-allowed transition-all duration-300 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">Capture & Verify</button>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div id="profile-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6 transform scale-95 transition-transform duration-300" id="profile-modal-content">
            <h2 class="text-xl font-semibold text-slate-900 mb-6">Profile Settings</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profile-form">
                @csrf
                @method('PUT')

                <!-- Profile Picture -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Profile Picture</label>

                    <!-- Current Profile Picture / Preview -->
                    <div class="flex justify-center mb-4">
                        @if (Auth::user()->profile_picture)
                            <img id="current-profile-picture" src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}" class="size-20 rounded-full object-cover select-none pointer-events-none">
                        @else
                            <div id="current-profile-picture" class="size-20 rounded-full flex items-center justify-center text-2xl font-semibold bg-slate-200 text-slate-600 select-none pointer-events-none">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <!-- File Input -->
                    <div class="flex justify-center">
                        <label for="profile-picture-input" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200 transition-colors">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Choose File
                        </label>
                        <input type="file" name="profile_picture" id="profile-picture-input" accept="image/*" class="hidden">
                    </div>
                    <p class="text-xs text-slate-500 mt-1 text-center">JPG, PNG, GIF up to 2MB</p>

                    <!-- Face Recognition Setup Button -->
                    <div class="flex justify-center mt-3">
                        <button type="button" onclick="openFaceRecognitionModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-sm font-medium hover:bg-blue-200 transition-colors">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Setup Face Recognition
                        </button>
                    </div>

                    <!-- Cropper Modal -->
                    <div id="cropper-modal" class="hidden mt-4">
                        <div class="border border-slate-300 rounded-lg overflow-hidden mb-4 flex justify-center" style="max-height: 400px;">
                            <img id="image-to-crop" src="" alt="Crop image" class="max-w-full">
                        </div>
                        <div class="flex items-center justify-center gap-4 mb-4">
                            <div class="text-sm text-slate-600">Preview:</div>
                            <div class="size-20 rounded-full overflow-hidden border-2 border-slate-300">
                                <img id="crop-preview" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="cancelCrop()" class="flex-1 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Cancel</button>
                            <button type="button" onclick="confirmCrop()" class="flex-1 py-2 rounded-lg font-medium text-white bg-slate-900 hover:bg-slate-800 transition-colors">Crop & Save</button>
                        </div>
                    </div>

                    <!-- Hidden input for cropped image -->
                    <input type="hidden" name="cropped_profile_picture" id="cropped-profile-picture">
                </div>

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ Auth::user()->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:border-transparent">
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">New Password (optional)</label>
                    <input type="password" name="password" id="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:border-transparent" placeholder="Leave blank to keep current password">
                </div>

                <!-- Password Confirmation -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:border-transparent" placeholder="Leave blank to keep current password">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeProfileModal()" class="flex-1 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-lg font-medium text-white bg-slate-900 hover:bg-slate-800 transition-colors">Save Changes</button>
                </div>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-200">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-colors">Sign Out</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Face Recognition Setup Modal -->
    <div id="face-recognition-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6 transform scale-95 transition-transform duration-300" id="face-recognition-modal-content">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Face Recognition Setup</h2>
            <p class="text-slate-600 mb-4">Capture a clear photo of your face for identity verification during clock-in.</p>
            
            <div class="relative mb-4">
                <video id="face-recognition-video" class="w-full rounded-lg bg-slate-900" autoplay playsinline></video>
                <canvas id="face-recognition-canvas" class="hidden"></canvas>
                <div id="face-recognition-loading" class="absolute inset-0 flex items-center justify-center bg-slate-900/50">
                    <div class="text-white text-center">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Loading camera...</p>
                    </div>
                </div>
            </div>
            
            <div id="face-recognition-status" class="mb-4 bg-slate-100 text-slate-700 px-4 py-3 rounded-lg text-center hidden">
                <span id="face-recognition-status-text">Detecting face...</span>
            </div>
            
            <div class="flex gap-3 relative z-10">
                <button onclick="closeFaceRecognitionModal()" class="flex-1 py-3 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">Cancel</button>
                <button id="capture-recognition-btn" onclick="captureRecognitionFace()" disabled class="flex-1 py-3 rounded-lg font-medium text-white disabled:cursor-not-allowed transition-all duration-300 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">Capture Face</button>
            </div>
        </div>
    </div>

    <script>
        // Store routes as JavaScript variables
        const attendanceCheckInRoute = "{{ route('attendance.check-in') }}";
        const attendanceCheckOutRoute = "{{ route('attendance.check-out') }}";
        const attendanceStatusRoute = "{{ route('attendance.status') }}";

        // Face recognition variables
        let faceRecognitionStream = null;
        let faceRecognitionModelsLoaded = false;
        let userFaceDescriptor = null;

        // Attendance Check-in/Check-out
        document.addEventListener('DOMContentLoaded', function() {
            loadAttendanceStatus();
            loadFaceRecognitionModels();
            loadUserFaceDescriptor();
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
                    
                    // Show force check-in overlay
                    const overlay = document.getElementById('force-checkin-overlay');
                    if (overlay) {
                        overlay.classList.remove('hidden');
                        overlay.classList.add('flex');
                    }
                } else if (data.status === 'checked_in') {
                    statusText.textContent = `Checked in at ${data.check_in_time}`;
                    checkInBtn.classList.add('hidden');
                    checkOutBtn.classList.remove('hidden');
                    
                    // Hide force check-in overlay
                    const overlay = document.getElementById('force-checkin-overlay');
                    if (overlay) {
                        overlay.classList.add('hidden');
                        overlay.classList.remove('flex');
                    }
                } else if (data.status === 'checked_out') {
                    statusText.textContent = `Checked out at ${data.check_out_time} (${data.hours_worked} hours)`;
                    checkInBtn.classList.add('hidden');
                    checkOutBtn.classList.add('hidden');
                    
                    // Hide force check-in overlay
                    const overlay = document.getElementById('force-checkin-overlay');
                    if (overlay) {
                        overlay.classList.add('hidden');
                        overlay.classList.remove('flex');
                    }
                } else if (data.status === 'error') {
                    statusText.textContent = 'Error loading status';
                    checkInBtn.classList.remove('hidden');
                    checkOutBtn.classList.add('hidden');
                } else {
                    // Unknown status, default to showing check-in button
                    console.warn('Unknown attendance status:', data.status);
                    statusText.textContent = 'Status unknown';
                    checkInBtn.classList.remove('hidden');
                    checkOutBtn.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error loading attendance status:', error);
            }
        }

        async function checkIn() {
            // Open face verification modal instead of directly checking in
            openFaceVerification();
        }

        // Face Verification
        let videoStream = null;
        let faceDetectionInterval = null;
        let isFaceDetected = false;

        async function openFaceVerification() {
            const modal = document.getElementById('face-verification-modal');
            const modalContent = document.getElementById('face-verification-modal-content');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);

            // Start camera
            await startCamera();
        }

        async function closeFaceVerification() {
            const modal = document.getElementById('face-verification-modal');
            const modalContent = document.getElementById('face-verification-modal-content');
            
            // Stop camera
            stopCamera();
            
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        async function startCamera() {
            const video = document.getElementById('face-video');
            const loading = document.getElementById('face-loading');
            const status = document.getElementById('face-status');
            const statusText = document.getElementById('face-status-text');
            const captureBtn = document.getElementById('capture-face-btn');
            
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    } 
                });
                
                video.srcObject = videoStream;
                
                video.onloadedmetadata = () => {
                    loading.classList.add('hidden');
                    status.classList.remove('hidden');
                    statusText.textContent = 'Position your face in the center';
                    
                    // Start face detection
                    startFaceDetection();
                };
            } catch (err) {
                console.error('Error accessing camera:', err);
                loading.classList.add('hidden');
                status.classList.remove('hidden');
                statusText.textContent = 'Camera access denied. Please allow camera access.';
                alert('Unable to access camera. Please allow camera permissions and try again.');
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
                faceDetectionInterval = null;
            }
            
            isFaceDetected = false;
            const captureBtn = document.getElementById('capture-face-btn');
            if (captureBtn) captureBtn.disabled = true;
        }

        async function startFaceDetection() {
            const video = document.getElementById('face-video');
            const canvas = document.getElementById('face-canvas');
            const statusText = document.getElementById('face-status-text');
            const captureBtn = document.getElementById('capture-face-btn');
            
            // Load face detection models
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            } catch (err) {
                console.error('Error loading face detection models:', err);
                statusText.textContent = 'Face detection unavailable. Proceeding without verification.';
                captureBtn.disabled = false;
                return;
            }

            // Detect faces continuously
            faceDetectionInterval = setInterval(async () => {
                if (!videoStream) return;

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                
                if (detections.length > 0) {
                    isFaceDetected = true;
                    statusText.textContent = 'Face detected! Ready to capture.';
                    statusText.classList.add('text-emerald-400');
                    statusText.classList.remove('text-amber-400');
                    captureBtn.disabled = false;
                } else {
                    isFaceDetected = false;
                    statusText.textContent = 'No face detected. Please position your face in the center.';
                    statusText.classList.add('text-amber-400');
                    statusText.classList.remove('text-emerald-400');
                    captureBtn.disabled = true;
                }
            }, 500);
        }

        async function captureFace() {
            const video = document.getElementById('face-video');
            const canvas = document.getElementById('face-canvas');
            const statusText = document.getElementById('face-status-text');
            
            // Capture image
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            // Convert to base64
            const imageData = canvas.toDataURL('image/jpeg');
            
            // Verify face is present
            if (!isFaceDetected) {
                statusText.textContent = 'No face detected. Please try again.';
                return;
            }

            // If user has face recognition set up, verify identity
            if (userFaceDescriptor) {
                const isMatch = await verifyFaceIdentity(canvas);
                if (!isMatch) {
                    statusText.textContent = 'Face does not match. Please try again.';
                    statusText.classList.add('text-red-400');
                    statusText.classList.remove('text-emerald-400', 'text-amber-400');
                    return;
                }
                statusText.textContent = 'Identity verified! Proceeding with check-in...';
            }
            
            // Proceed with check-in
            await performCheckIn(imageData);
        }

        async function verifyFaceIdentity(canvas) {
            try {
                // Load face recognition models if not loaded
                if (!faceRecognitionModelsLoaded) {
                    await loadFaceRecognitionModels();
                }

                // Detect face and get descriptor
                const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (!detection) {
                    console.log('No face detected for verification');
                    return false;
                }

                const capturedDescriptor = detection.descriptor;
                
                // Calculate Euclidean distance between descriptors
                const distance = faceapi.euclideanDistance(capturedDescriptor, userFaceDescriptor);
                
                console.log('Face recognition distance:', distance);
                
                // Threshold for face matching (typically 0.6 is a good threshold)
                const threshold = 0.6;
                return distance < threshold;
            } catch (err) {
                console.error('Error verifying face identity:', err);
                return false;
            }
        }

        async function performCheckIn(faceImage) {
            try {
                const response = await fetch(attendanceCheckInRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        face_image: faceImage
                    }),
                });
                
                if (response.ok) {
                    closeFaceVerification();
                    // Wait a moment for the database to update
                    setTimeout(() => {
                        loadAttendanceStatus();
                    }, 500);
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

        // Face Recognition Setup Functions
        async function loadFaceRecognitionModels() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                faceRecognitionModelsLoaded = true;
                console.log('Face recognition models loaded');
            } catch (err) {
                console.error('Error loading face recognition models:', err);
            }
        }

        async function loadUserFaceDescriptor() {
            try {
                const response = await fetch('/api/user-face-descriptor');
                if (response.ok) {
                    const data = await response.json();
                    if (data.face_descriptor) {
                        userFaceDescriptor = new Float32Array(JSON.parse(data.face_descriptor));
                        console.log('User face descriptor loaded');
                    }
                }
            } catch (err) {
                console.error('Error loading user face descriptor:', err);
            }
        }

        function openFaceRecognitionModal() {
            const modal = document.getElementById('face-recognition-modal');
            const content = document.getElementById('face-recognition-modal-content');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);

            startFaceRecognitionCamera();
        }

        function closeFaceRecognitionModal() {
            const modal = document.getElementById('face-recognition-modal');
            const content = document.getElementById('face-recognition-modal-content');
            
            stopFaceRecognitionCamera();
            
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        async function startFaceRecognitionCamera() {
            const video = document.getElementById('face-recognition-video');
            const loading = document.getElementById('face-recognition-loading');
            const status = document.getElementById('face-recognition-status');
            const statusText = document.getElementById('face-recognition-status-text');
            const captureBtn = document.getElementById('capture-recognition-btn');
            
            try {
                faceRecognitionStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    } 
                });
                
                video.srcObject = faceRecognitionStream;
                
                video.onloadedmetadata = () => {
                    loading.classList.add('hidden');
                    status.classList.remove('hidden');
                    statusText.textContent = 'Position your face in the center';
                    
                    // Start face detection for recognition setup
                    startFaceRecognitionDetection();
                };
            } catch (err) {
                console.error('Error accessing camera:', err);
                loading.classList.add('hidden');
                status.classList.remove('hidden');
                statusText.textContent = 'Camera access denied. Please allow camera access.';
                alert('Unable to access camera. Please allow camera permissions and try again.');
            }
        }

        function stopFaceRecognitionCamera() {
            if (faceRecognitionStream) {
                faceRecognitionStream.getTracks().forEach(track => track.stop());
                faceRecognitionStream = null;
            }
        }

        async function startFaceRecognitionDetection() {
            const video = document.getElementById('face-recognition-video');
            const statusText = document.getElementById('face-recognition-status-text');
            const captureBtn = document.getElementById('capture-recognition-btn');
            
            if (!faceRecognitionModelsLoaded) {
                await loadFaceRecognitionModels();
            }

            // Detect faces continuously
            const detectionInterval = setInterval(async () => {
                if (!faceRecognitionStream) {
                    clearInterval(detectionInterval);
                    return;
                }

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                
                if (detections.length > 0) {
                    statusText.textContent = 'Face detected! Ready to capture.';
                    statusText.classList.add('text-emerald-400');
                    statusText.classList.remove('text-amber-400');
                    captureBtn.disabled = false;
                } else {
                    statusText.textContent = 'No face detected. Please position your face in the center.';
                    statusText.classList.add('text-amber-400');
                    statusText.classList.remove('text-emerald-400');
                    captureBtn.disabled = true;
                }
            }, 500);
        }

        async function captureRecognitionFace() {
            const video = document.getElementById('face-recognition-video');
            const canvas = document.getElementById('face-recognition-canvas');
            const statusText = document.getElementById('face-recognition-status-text');
            
            // Capture image
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            // Convert to base64
            const imageData = canvas.toDataURL('image/jpeg');
            
            // Generate face descriptor
            try {
                const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (detection) {
                    const descriptor = Array.from(detection.descriptor);
                    
                    // Send to server
                    await saveFaceDescriptor(imageData, descriptor);
                } else {
                    statusText.textContent = 'No face detected. Please try again.';
                }
            } catch (err) {
                console.error('Error generating face descriptor:', err);
                statusText.textContent = 'Error processing face. Please try again.';
            }
        }

        async function saveFaceDescriptor(imageData, descriptor) {
            try {
                const response = await fetch('/profile/face-descriptor', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        profile_picture: imageData,
                        face_descriptor: JSON.stringify(descriptor)
                    }),
                });
                
                if (response.ok) {
                    closeFaceRecognitionModal();
                    alert('Face recognition setup completed successfully!');
                    location.reload();
                } else {
                    alert('Error saving face data. Please try again.');
                }
            } catch (error) {
                console.error('Error saving face descriptor:', error);
                alert('Error saving face data. Please try again.');
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
            
            if (countdownTextEl) countdownTextEl.innerHTML = 'Please wait <span class="font-bold text-red-600">' + countdownValue + '</span> seconds before confirming...';
            if (countdownBarEl) countdownBarEl.style.width = '100%';
            if (confirmBtnEl) {
                confirmBtnEl.disabled = true;
                confirmBtnEl.style.backgroundColor = '#cbd5e1';
                confirmBtnEl.style.color = '#64748b';
            }
            
            // Start countdown
            console.log('Starting countdown with value:', countdownValue);
            countdownInterval = setInterval(() => {
                countdownValue--;
                console.log('Countdown tick:', countdownValue);
                if (countdownTextEl) {
                    countdownTextEl.innerHTML = 'Please wait <span class="font-bold text-red-600">' + countdownValue + '</span> seconds before confirming...';
                    console.log('Updated countdown text to:', countdownValue);
                }
                if (countdownBarEl) countdownBarEl.style.width = `${(countdownValue / 5) * 100}%`;
                
                if (countdownValue <= 0) {
                    clearInterval(countdownInterval);
                    console.log('Countdown finished');
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



        // Profile Modal
        window.openProfileModal = function() {
            const modal = document.getElementById('profile-modal');
            const modalContent = document.getElementById('profile-modal-content');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }

        window.closeProfileModal = function() {
            const modal = document.getElementById('profile-modal');
            const modalContent = document.getElementById('profile-modal-content');

            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        // Close modal when clicking outside
        document.getElementById('profile-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });

        // Image Cropping
        let cropper = null;
        const profilePictureInput = document.getElementById('profile-picture-input');
        const cropperModal = document.getElementById('cropper-modal');
        const imageToCrop = document.getElementById('image-to-crop');
        const cropPreview = document.getElementById('crop-preview');
        const croppedProfilePicture = document.getElementById('cropped-profile-picture');
        const currentProfilePicture = document.getElementById('current-profile-picture');

        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imageToCrop.src = event.target.result;
                    cropperModal.classList.remove('hidden');

                    if (cropper) {
                        cropper.destroy();
                    }

                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1,
                        viewMode: 1,
                        minCropBoxWidth: 200,
                        minCropBoxHeight: 200,
                        crop: function(event) {
                            const canvas = cropper.getCroppedCanvas({
                                width: 200,
                                height: 200,
                            });
                            cropPreview.src = canvas.toDataURL();
                        },
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        function confirmCrop() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 200,
                    height: 200,
                });

                canvas.toBlob(function(blob) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        croppedProfilePicture.value = event.target.result;
                        // Update the center image with the cropped preview
                        currentProfilePicture.src = cropPreview.src;
                        // Clear the file input to prevent conflicts
                        profilePictureInput.value = '';
                    };
                    reader.readAsDataURL(blob);
                });

                cropper.destroy();
                cropper = null;
                cropperModal.classList.add('hidden');
            }
        }

        function cancelCrop() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            cropperModal.classList.add('hidden');
            profilePictureInput.value = '';
        }
    </script>
    <!-- Force Check In Overlay -->
    <div id="force-checkin-overlay" class="fixed inset-0 bg-slate-900/95 backdrop-blur-md z-[9990] hidden flex-col items-center justify-center transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8 text-center transform transition-transform duration-300 scale-100">
            <div class="w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-3">Shift Check-In Required</h2>
            <p class="text-slate-600 mb-8 text-lg">You must check in to start your shift before accessing the system.</p>
            <button onclick="openFaceVerification()" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                Check In Now
            </button>
        </div>
    </div>

</body>
</html>
