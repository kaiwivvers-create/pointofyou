<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'cropped_profile_picture' => 'nullable|string',
        ]);

        // Update name
        $user->name = $request->name;

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update profile picture if provided
        if ($request->filled('cropped_profile_picture')) {
            // Handle base64 cropped image
            $imageData = $request->cropped_profile_picture;
            $imageData = preg_replace('/^data:image\/(\w+);base64,/', '', $imageData);
            $imageData = base64_decode($imageData);

            // Delete old profile picture if exists
            if ($user->employee && $user->employee->profile_picture) {
                Storage::disk('public')->delete($user->employee->profile_picture);
            }

            // Store new profile picture
            $fileName = 'profile-' . $user->id . '-' . time() . '.png';
            $path = 'profile-pictures/' . $fileName;
            Storage::disk('public')->put($path, $imageData);

            // Create or update employee record
            if (!$user->employee) {
                $employee = new \App\Models\Employee();
                $employee->user_id = $user->id;
                $employee->profile_picture = $path;
                $employee->save();
            } else {
                $user->employee->profile_picture = $path;
                $user->employee->save();
            }
        } elseif ($request->hasFile('profile_picture')) {
            // Handle regular file upload (fallback)
            // Delete old profile picture if exists
            if ($user->employee && $user->employee->profile_picture) {
                Storage::disk('public')->delete($user->employee->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');

            // Create or update employee record
            if (!$user->employee) {
                $employee = new \App\Models\Employee();
                $employee->user_id = $user->id;
                $employee->profile_picture = $path;
                $employee->save();
            } else {
                $user->employee->profile_picture = $path;
                $user->employee->save();
            }
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
