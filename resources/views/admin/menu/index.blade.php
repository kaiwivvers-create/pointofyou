@extends('layouts.staff')

@section('title', 'Menu')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Menu</h1>
            <p class="staff-page-subtitle">Add and edit food & drinks for table ordering.</p>
        </div>
        <a href="{{ route('admin.menu.create') }}" class="staff-btn-primary">Add item</a>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menuItems as $item)
                        <tr>
                            <td>
                                <span class="mr-2 text-lg">{{ $item->emoji }}</span>
                                <span class="font-semibold text-amber-950">{{ $item->name }}</span>
                            </td>
                            <td><span class="capitalize">{{ $item->category }}</span></td>
                            <td class="font-semibold text-amber-900">{{ $item->formattedPrice() }}</td>
                            <td>
                                @if ($item->is_available)
                                    <span class="staff-badge-green">Available</span>
                                @else
                                    <span class="staff-badge-muted">Hidden</span>
                                @endif
                            </td>
                            <td class="text-right space-x-4">
                                <a href="{{ route('admin.menu.edit', $item) }}" class="staff-link">Edit</a>
                                <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-stone-500">No menu items yet. Add your first bake!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
