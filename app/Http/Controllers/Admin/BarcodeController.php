<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $menuItems = MenuItem::orderBy('name')->get();
        $gifts = Gift::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $items = collect();

        foreach ($menuItems as $m) {
            $items->push([
                'id'       => $m->id,
                'type'     => 'menu_item',
                'name'     => $m->name,
                'category' => strtoupper($m->category ?? 'Menu Item'),
                'barcode'  => $m->barcode,
            ]);
        }

        foreach ($gifts as $g) {
            $items->push([
                'id'       => $g->id,
                'type'     => 'gift',
                'name'     => $g->name,
                'category' => 'GIFT',
                'barcode'  => $g->barcode,
            ]);
        }

        foreach ($products as $p) {
            $items->push([
                'id'       => $p->id,
                'type'     => 'product',
                'name'     => $p->name,
                'category' => 'INVENTORY / TAKEOUT',
                'barcode'  => $p->barcode,
            ]);
        }

        $search = $request->input('search');
        if ($search) {
            $items = $items->filter(function($item) use ($search) {
                return stripos($item['name'], $search) !== false ||
                       stripos($item['category'], $search) !== false ||
                       stripos($item['barcode'] ?? '', $search) !== false;
            });
        }

        $page = $request->get('page', 1);
        $perPage = 15;
        $total = $items->count();
        $paginatedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($paginatedItems, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return view('admin.barcodes.index', compact('paginator', 'search'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:menu_item,gift,product',
            'barcode' => 'nullable|string|numeric', // only numbers per user request
        ], [
            'barcode.numeric' => 'Barcode must contain only numbers.'
        ]);

        $barcode = empty($validated['barcode']) ? null : $validated['barcode'];

        // Check for duplicates
        if ($barcode) {
            $existsInMenu = MenuItem::where('barcode', $barcode)->where('id', '!=', $validated['type'] === 'menu_item' ? $validated['id'] : 0)->exists();
            $existsInGifts = Gift::where('barcode', $barcode)->where('id', '!=', $validated['type'] === 'gift' ? $validated['id'] : 0)->exists();
            $existsInProducts = Product::where('barcode', $barcode)->where('id', '!=', $validated['type'] === 'product' ? $validated['id'] : 0)->exists();
            
            if ($existsInMenu || $existsInGifts || $existsInProducts) {
                return response()->json(['success' => false, 'message' => 'This barcode is already assigned to another item.']);
            }
        }

        if ($validated['type'] === 'menu_item') {
            $item = MenuItem::findOrFail($validated['id']);
        } elseif ($validated['type'] === 'gift') {
            $item = Gift::findOrFail($validated['id']);
        } else {
            $item = Product::findOrFail($validated['id']);
        }

        $item->update(['barcode' => $barcode]);

        return response()->json(['success' => true, 'message' => 'Barcode updated.']);
    }
}
