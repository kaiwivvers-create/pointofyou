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
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $fileName = 'profile-' . $user->id . '-' . time() . '.png';
            $path = 'profile-pictures/' . $fileName;
            Storage::disk('public')->put($path, $imageData);

            $user->profile_picture = $path;
        } elseif ($request->hasFile('profile_picture')) {
            // Handle regular file upload (fallback)
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');

            $user->profile_picture = $path;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function saveFaceDescriptor(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'profile_picture' => 'required|string',
            'face_descriptor' => 'required|string',
        ]);

        // Handle base64 image
        $imageData = $request->profile_picture;
        $imageData = preg_replace('/^data:image\/(\w+);base64,/', '', $imageData);
        $imageData = base64_decode($imageData);

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new profile picture
        $fileName = 'profile-' . $user->id . '-' . time() . '.png';
        $path = 'profile-pictures/' . $fileName;
        Storage::disk('public')->put($path, $imageData);

        // Update user with profile picture and face descriptor
        $user->profile_picture = $path;
        $user->face_descriptor = $request->face_descriptor;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function getFaceDescriptor()
    {
        $user = auth()->user();
        
        return response()->json([
            'face_descriptor' => $user->face_descriptor,
            'has_face_recognition' => !empty($user->face_descriptor)
        ]);
    }
}
