<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show');
    }

    /**
     * Handle account settings update (name, email, password).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'string'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Password change — require current password
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Account settings saved.');
    }

    /**
     * Handle profile photo upload.
     * Expects a multipart POST with field "photo" (image, max 2 MB).
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old photo if one exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Store new photo under storage/app/public/profile-photos/
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->profile_photo = $path;
        $user->save();

        return back()->with('success', 'Profile photo updated.');
    }

    /**
     * Handle banner colour change.
     * Expects JSON or form-encoded body with field "banner_color" (hex, e.g. "#667eea").
     */
    public function updateBannerColor(Request $request)
    {
        $request->validate([
            'banner_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $user = Auth::user();
        $user->banner_color = $request->banner_color;
        $user->save();

        return back()->with('success', 'Banner colour updated.');
    }
}