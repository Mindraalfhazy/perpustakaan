<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'title' => 'Profil Saya',
            'breadcrumb' => ['Profil Saya'],
            'user' => $request->user()->loadMissing('member'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:300'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'nik' => [
                Rule::requiredIf($user->isAdmin()),
                'nullable',
                'digits:16',
                Rule::unique('users', 'nik')->ignore($user->id),
            ],
        ], [
            'nik.required' => 'NIK wajib diisi untuk akun administrator/guru.',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        if (!$user->isAdmin()) {
            unset($validated['nik']);
        }

        $user->forceFill($validated)->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
