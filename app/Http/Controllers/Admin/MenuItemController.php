<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::query()->orderBy('category')->orderBy('name')->get();

        return view('admin.menu.index', compact('menuItems'));
    }

    public function create(): View
    {
        return view('admin.menu.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'is_available' => ['boolean'],
        ]);

        $validated['is_available'] = $request->boolean('is_available', true);

        MenuItem::create($validated);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item added.');
    }

    public function edit(MenuItem $menu): View
    {
        return view('admin.menu.edit', ['menuItem' => $menu]);
    }

    public function update(Request $request, MenuItem $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'is_available' => ['boolean'],
        ]);

        $validated['is_available'] = $request->boolean('is_available');

        $menu->update($validated);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menu.index')->with('success', 'Menu item removed.');
    }
}
