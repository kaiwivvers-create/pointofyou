<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Packet;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PacketController extends Controller
{
    public function index()
    {
        $packets = Packet::ordered()->with('items')->get();
        $menuItems = MenuItem::where('is_available', true)->orderBy('name')->get(['id', 'name', 'price']);
        return view('admin.packets.index', compact('packets', 'menuItems'));
    }

    public function create()
    {
        $menuItems = MenuItem::active()->ordered()->get();
        return view('admin.packets.create', compact('menuItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'cropped_image' => 'nullable|string',
            'fixed_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'order' => 'integer',
            'items' => 'array',
            'items.*.menu_item_id' => 'nullable|exists:menu_items,id',
            'items.*.gift_id' => 'nullable|exists:gifts,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($request->filled('cropped_image')) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image));
            $imageName = 'packet_' . time() . '.jpg';
            $path = 'packets/' . $imageName;
            Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('packets', 'public');
        }

        $packet = Packet::create($validated);

        if (isset($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (!empty($item['menu_item_id'])) {
                    $packet->items()->attach($item['menu_item_id'], ['quantity' => $item['quantity'], 'gift_id' => null]);
                } elseif (!empty($item['gift_id'])) {
                    $packet->items()->attach($item['menu_item_id'] ?? null, ['quantity' => $item['quantity'], 'gift_id' => $item['gift_id']]);
                }
            }
        }

        return redirect()->route('admin.packets.index')->with('success', 'Packet created successfully.');
    }

    public function edit(Packet $packet)
    {
        $menuItems = MenuItem::active()->ordered()->get();
        return view('admin.packets.edit', compact('packet', 'menuItems'));
    }

    public function update(Request $request, Packet $packet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'cropped_image' => 'nullable|string',
            'fixed_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'order' => 'integer',
            'items' => 'array',
            'items.*.menu_item_id' => 'nullable|exists:menu_items,id',
            'items.*.gift_id' => 'nullable|exists:gifts,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($request->filled('cropped_image')) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image));
            $imageName = 'packet_' . time() . '.jpg';
            $path = 'packets/' . $imageName;
            Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('packets', 'public');
        }

        $packet->update($validated);

        if (isset($validated['items'])) {
            $packet->items()->detach();
            foreach ($validated['items'] as $item) {
                if (!empty($item['menu_item_id'])) {
                    $packet->items()->attach($item['menu_item_id'], ['quantity' => $item['quantity'], 'gift_id' => null]);
                } elseif (!empty($item['gift_id'])) {
                    $packet->items()->attach($item['menu_item_id'] ?? null, ['quantity' => $item['quantity'], 'gift_id' => $item['gift_id']]);
                }
            }
        }

        return redirect()->route('admin.packets.index')->with('success', 'Packet updated successfully.');
    }

    public function destroy(Packet $packet)
    {
        $packet->delete();
        return redirect()->route('admin.packets.index')->with('success', 'Packet deleted successfully.');
    }
}
