<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Scanner - {{ $userName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-slate-900 min-h-screen">
    <div class="max-w-md mx-auto p-4">
        <!-- Header -->
        <div class="bg-slate-800 rounded-lg p-4 mb-4">
            <h1 class="text-white text-xl font-bold mb-2">Mobile Scanner</h1>
            <p class="text-slate-400 text-sm">Connected to: {{ $userName }}</p>
            <p class="text-slate-500 text-xs mt-1">Session: {{ $sessionCode }}</p>
        </div>

        <!-- Scanner -->
        <div class="bg-slate-800 rounded-lg p-4 mb-4">
            <div id="qr-reader" class="w-full bg-black rounded-lg overflow-hidden" style="min-height: 300px;"></div>
            <p class="text-center text-slate-400 text-sm mt-4">Point camera at barcode to scan</p>
        </div>

        <!-- Manual Input -->
        <div class="bg-slate-800 rounded-lg p-4 mb-4">
            <p class="text-slate-400 text-sm mb-2">Or enter barcode manually:</p>
            <div class="flex gap-2">
                <input type="text" id="manual-barcode" class="flex-1 px-3 py-2 bg-slate-700 text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter barcode...">
                <button onclick="submitManualBarcode()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
            </div>
        </div>

        <!-- Recent Scans -->
        <div class="bg-slate-800 rounded-lg p-4">
            <h2 class="text-white text-sm font-semibold mb-3">Recent Scans</h2>
            <div id="recent-scans" class="space-y-2">
                <p class="text-slate-500 text-xs">No items scanned yet</p>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner = null;
        let isScanning = false;
        const sessionCode = '{{ $sessionCode }}';
        let recentScansData = JSON.parse(localStorage.getItem(`recent_scans_${sessionCode}`) || '[]');
        let lastScanTime = 0;
        const SCAN_COOLDOWN = 1000; // 1 second cooldown

        function onScanSuccess(decodedText, decodedResult) {
            const now = Date.now();
            if (now - lastScanTime < SCAN_COOLDOWN) {
                console.log('Scan blocked by cooldown');
                return;
            }
            lastScanTime = now;
            
            console.log('Barcode scanned:', decodedText);
            addToCart(decodedText);
        }

        function onScanFailure(error) {
            // Suppress the "No MultiFormat Readers were able to detect the code" warning
            // as this is normally thrown every frame there is no barcode/QR code visible.
        }

        async function addToCart(barcode) {
            try {
                const response = await fetch(`{{ url('/device-sessions') }}/${sessionCode}/add-to-cart`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ barcode: barcode }),
                });

                const data = await response.json();

                if (data.success) {
                    addRecentScan(data.item);
                    // Play success sound or vibration
                    if (navigator.vibrate) {
                        navigator.vibrate(100);
                    }
                } else {
                    alert(data.error || 'Failed to add item');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                alert('Error adding item to cart');
            }
        }

        function addRecentScan(item) {
            const timestamp = new Date().toLocaleTimeString();
            
            // Check if item already exists in recent scans
            const existingItem = recentScansData.find(s => s.name === item.name);
            if (existingItem) {
                existingItem.quantity++;
                existingItem.timestamp = timestamp;
            } else {
                recentScansData.unshift({
                    name: item.name,
                    price: item.price,
                    quantity: 1,
                    timestamp: timestamp
                });
            }
            
            // Keep only last 10 scans
            if (recentScansData.length > 10) {
                recentScansData = recentScansData.slice(0, 10);
            }
            
            // Save to localStorage
            localStorage.setItem(`recent_scans_${sessionCode}`, JSON.stringify(recentScansData));
            
            // Render recent scans
            renderRecentScans();
        }

        function renderRecentScans() {
            const recentScans = document.getElementById('recent-scans');
            
            if (recentScansData.length === 0) {
                recentScans.innerHTML = '<p class="text-slate-500 text-xs">No items scanned yet</p>';
                return;
            }
            
            recentScans.innerHTML = '';
            
            recentScansData.forEach((scan, index) => {
                const scanItem = document.createElement('div');
                scanItem.className = 'bg-slate-700 rounded-lg p-3 flex justify-between items-center';
                scanItem.innerHTML = `
                    <div class="flex-1">
                        <p class="text-white text-sm font-medium">${scan.name}</p>
                        <p class="text-slate-400 text-xs">${scan.timestamp}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQuantity(${index}, -1)" class="w-8 h-8 rounded-full bg-slate-600 text-white font-bold hover:bg-slate-500">-</button>
                        <span class="text-white font-semibold w-6 text-center">${scan.quantity}</span>
                        <button onclick="updateQuantity(${index}, 1)" class="w-8 h-8 rounded-full bg-slate-600 text-white font-bold hover:bg-slate-500">+</button>
                    </div>
                    <p class="text-emerald-400 text-sm font-semibold ml-2">$${(parseFloat(scan.price) * scan.quantity).toFixed(2)}</p>
                `;
                recentScans.appendChild(scanItem);
            });
        }

        function updateQuantity(index, delta) {
            const scan = recentScansData[index];
            scan.quantity += delta;
            
            if (scan.quantity <= 0) {
                recentScansData.splice(index, 1);
            }
            
            localStorage.setItem(`recent_scans_${sessionCode}`, JSON.stringify(recentScansData));
            renderRecentScans();
        }

        function submitManualBarcode() {
            const input = document.getElementById('manual-barcode');
            const barcode = input.value.trim();
            
            if (barcode) {
                addToCart(barcode);
                input.value = '';
            }
        }

        async function startScanner() {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            try {
                await html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                );
                isScanning = true;
            } catch (err) {
                console.error("Error starting scanner:", err);
                alert("Unable to start camera. Please check permissions.");
            }
        }

        // Start scanner on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderRecentScans();
            startScanner();
        });
    </script>
    @include('partials.translator', ['isFloating' => true])
</body>
</html>
