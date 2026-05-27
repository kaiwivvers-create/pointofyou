<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::getOrdered();
        return view('admin.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:64|unique:menu_categories,name|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:64',
        ]);

        $maxOrder = MenuCategory::max('sort_order') ?? 0;

        MenuCategory::create([
            'name'       => strtolower($validated['name']),
            'label'      => $validated['label'],
            'sort_order' => $maxOrder + 1,
            'is_visible' => true,
        ]);

        return back()->with('success', 'Category "' . $validated['label'] . '" added.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order'          => 'required|array',
            'order.*.id'     => 'required|integer|exists:menu_categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['order'] as $item) {
            MenuCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleVisibility(MenuCategory $category)
    {
        $category->update(['is_visible' => !$category->is_visible]);
        return back()->with('success', '"' . $category->label . '" is now ' . ($category->is_visible ? 'visible' : 'hidden') . '.');
    }

    public function destroy(MenuCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category "' . $category->label . '" removed.');
    }
}
