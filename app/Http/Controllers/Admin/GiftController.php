<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::ordered()->get();
        return view('admin.gifts.index', compact('gifts'));
    }

    public function create()
    {
        return view('admin.gifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'cropped_image' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->filled('cropped_image')) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image));
            $imageName = 'gift_' . time() . '.jpg';
            $path = 'gifts/' . $imageName;
            Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('gifts', 'public');
        }

        Gift::create($validated);

        return redirect()->route('admin.gifts.index')->with('success', 'Gift created successfully.');
    }

    public function edit(Gift $gift)
    {
        return view('admin.gifts.edit', compact('gift'));
    }

    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'cropped_image' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->filled('cropped_image')) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image));
            $imageName = 'gift_' . time() . '.jpg';
            $path = 'gifts/' . $imageName;
            Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('gifts', 'public');
        }

        $gift->update($validated);

        return redirect()->route('admin.gifts.index')->with('success', 'Gift updated successfully.');
    }

    public function destroy(Gift $gift)
    {
        $gift->delete();
        return redirect()->route('admin.gifts.index')->with('success', 'Gift deleted successfully.');
    }

    public function stockMovement(Request $request)
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $gift = Gift::findOrFail($validated['gift_id']);
        
        $currentStock = $gift->stock_quantity;
        $quantity = $validated['quantity'];
        
        if ($validated['type'] === 'in') {
            $gift->stock_quantity += $quantity;
        } elseif ($validated['type'] === 'out') {
            if ($currentStock < $quantity) {
                return back()->with('error', 'Insufficient stock for this operation.');
            }
            $gift->stock_quantity -= $quantity;
        } elseif ($validated['type'] === 'adjustment') {
            $gift->stock_quantity = $quantity;
        }

        $gift->save();

        return back()->with('success', 'Stock movement recorded successfully.');
    }

    public function inventoryUpdate(Request $request)
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'sku' => 'nullable|string|max:100',
            'cost' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'order' => 'required|integer',
        ]);

        $gift = Gift::findOrFail($validated['gift_id']);
        
        $gift->update([
            'sku' => $validated['sku'] ?? null,
            'cost' => $validated['cost'],
            'purchase_price' => $validated['purchase_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'order' => $validated['order'],
        ]);

        return redirect()->back()->with('success', 'Gift updated successfully.');
    }
}
