<?php

namespace App\Http\Controllers;

use App\Models\DeviceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceSessionController extends Controller
{
    public function create(Request $request)
    {
        try {
            // Generate unique session code
            $sessionCode = strtoupper(Str::random(8));
            
            // Create session that expires in 1 hour
            $session = DeviceSession::create([
                'session_code' => $sessionCode,
                'user_id' => auth()->id(),
                'expires_at' => now()->addHour(),
                'is_active' => true,
            ]);
            
            return response()->json([
                'session_code' => $session->session_code,
                'expires_at' => $session->expires_at,
                'qr_url' => url('/mobile-scan/' . $session->session_code),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 404);
        }
        
        return response()->json([
            'session_code' => $session->session_code,
            'expires_at' => $session->expires_at,
            'user_name' => $session->user->name,
        ]);
    }

    public function scanPage($sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$session) {
            abort(404, 'Invalid or expired session');
        }
        
        return view('mobile-scan.index', [
            'sessionCode' => $session->session_code,
            'userName' => $session->user->name,
        ]);
    }

    public function addToCart(Request $request, $sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 404);
        }
        
        $request->validate([
            'barcode' => 'required|string',
        ]);
        
        // Find item by barcode across all tables
        $item = \App\Models\MenuItem::where('barcode', $request->barcode)->first();
        $itemType = 'menu_item';
        
        if (!$item) {
            $item = \App\Models\Gift::where('barcode', $request->barcode)->first();
            $itemType = 'gift';
        }
        
        if (!$item) {
            $item = \App\Models\Product::where('barcode', $request->barcode)->first();
            $itemType = 'product';
        }
        
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        
        // Get price based on item type
        $price = 0;
        if ($itemType === 'menu_item') {
            $price = $item->price ?? 0;
        } elseif ($itemType === 'gift') {
            $price = $item->cost ?? 0;
        } elseif ($itemType === 'product') {
            $price = $item->cost ?? $item->price ?? 0;
        }
        
        // Store in session for the POS to pick up
        $cartItems = session('device_cart_' . $sessionCode, []);
        $cartItems[] = [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $price,
            'barcode' => $item->barcode,
            'type' => $itemType,
            'added_at' => now()->toISOString(),
        ];
        session(['device_cart_' . $sessionCode => $cartItems]);
        
        return response()->json([
            'success' => true,
            'item' => [
                'name' => $item->name,
                'price' => $price,
            ],
        ]);
    }

    public function getCartItems($sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 404);
        }
        
        $cartItems = session('device_cart_' . $sessionCode, []);
        
        return response()->json([
            'items' => $cartItems,
        ]);
    }

    public function clearCart($sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 404);
        }
        
        session(['device_cart_' . $sessionCode => []]);
        
        return response()->json(['success' => true]);
    }

    public function deactivate(Request $request, $sessionCode)
    {
        $session = DeviceSession::where('session_code', $sessionCode)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }
        
        $session->update(['is_active' => false]);
        
        return response()->json(['success' => true]);
    }

    public function activeSessions()
    {
        $sessions = DeviceSession::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->get();
        
        return response()->json([
            'sessions' => $sessions->map(function($session) {
                return [
                    'session_code' => $session->session_code,
                    'expires_at' => $session->expires_at,
                    'qr_url' => url('/mobile-scan/' . $session->session_code),
                ];
            }),
        ]);
    }
}
