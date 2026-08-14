<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Show', [
            'employee' => $user->employee ? [
                'id'                => $user->employee->id,
                'first_name'        => $user->employee->first_name,
                'middle_name'       => $user->employee->middle_name,
                'last_name'         => $user->employee->last_name,
                'birthdate'         => $user->employee->birthdate?->format('Y-m-d'),
                'gender'            => $user->employee->gender,
                'civil_status'      => $user->employee->civil_status,
                'contact_number'    => $user->employee->contact_number,
                'address'           => $user->employee->address,
                'department'        => $user->employee->department,
                'position'          => $user->employee->position,
                'employment_status' => $user->employee->employment_status,
                'date_hired'        => $user->employee->date_hired?->format('Y-m-d'),
                'salary_type'       => $user->employee->salary_type,
                'sss_number'        => $user->employee->sss_number,
                'philhealth_number' => $user->employee->philhealth_number,
                'pagibig_number'    => $user->employee->pagibig_number,
                'tin_number'        => $user->employee->tin_number,
            ] : null,
        ]);
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
        Storage::disk('s3')->delete($user->profile_photo); // changed
    }

    // Store new photo in bucket
    $path = $request->file('photo')->storePublicly('profile-photos', 's3'); // changed

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
    public function updatePersonal(Request $request)
{
    $user     = Auth::user();
    $employee = $user->employee;

    if (!$employee) {
        return back()->with('error', 'No employee record found.');
    }

    $request->validate([
        'first_name'     => ['required', 'string', 'max:100'],
        'middle_name'    => ['nullable', 'string', 'max:100'],
        'last_name'      => ['required', 'string', 'max:100'],
        'birthdate'      => ['required', 'date', 'before:today'],
        'gender'         => ['required', 'in:Male,Female,Other'],
        'civil_status'   => ['required', 'in:Single,Married,Widowed,Separated'],
        'contact_number' => ['required', 'string', 'max:20'],
        'address'        => ['required', 'string'],
    ]);

    $employee->update($request->only([
        'first_name', 'middle_name', 'last_name',
        'birthdate', 'gender', 'civil_status',
        'contact_number', 'address',
    ]));

    return back()->with('success', 'Personal information updated.');
}
}