@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100" role="alert">
        {{ session('error') }}
    </div>
@endif
