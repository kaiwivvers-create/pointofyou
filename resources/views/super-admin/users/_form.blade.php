@php
    $user = $user ?? null;
    $isEdit = $user !== null;
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="staff-label">Name</label>
        <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $user?->name) }}" class="staff-input">
    </div>
    <div>
        <label for="email" class="staff-label">Email</label>
        <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user?->email) }}" class="staff-input">
    </div>
    <div>
        <label for="password" class="staff-label">
            {{ $isEdit ? 'New password' : 'Password' }}
            @if ($isEdit)
                <span class="font-normal text-slate-500">(leave blank to keep current)</span>
            @endif
        </label>
        <input id="password" name="password" type="password" maxlength="255" @if (! $isEdit) required @endif class="staff-input" autocomplete="new-password">
    </div>
    @if (! $isEdit)
    <div>
        <label for="password_confirmation" class="staff-label">Confirm Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required maxlength="255" class="staff-input" autocomplete="new-password">
    </div>
    @endif
    <div>
        <label for="role" class="staff-label">Role</label>
        <select id="role" name="role" required class="staff-input">
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role', $user?->role_id) == $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
</div>
