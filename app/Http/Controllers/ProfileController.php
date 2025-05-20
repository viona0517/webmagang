<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\InternshipRegistration; // Model untuk dokumen user

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;

        // Simpan foto baru jika ada
        if ($request->hasFile('photo')) {
            // Hapus foto lama kalau ada
            if ($user->photo && Storage::exists('public/photos/' . $user->photo)) {
                Storage::delete('public/photos/' . $user->photo);
            }

            $photoName = time() . '.' . $request->photo->extension();
            $request->photo->storeAs('public/photos', $photoName);
            $user->photo = $photoName;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function show()
    {
        return view('profile.show', [
            'user' => Auth::user()
        ]);
    }
    
    public function profile()
    {
        $user = Auth::user(); // Ambil data user yang sedang login
        $registration = InternshipRegistration::all();
    
        return view('profile.show', compact('user', 'registration'));
    }
    
}