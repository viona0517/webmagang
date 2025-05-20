<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $upload = Upload::where('user_id', $user->id)->first();

        // Ambil semua task yang sudah diassign ke user
        $tasks = $user->tasks()->withPivot('status', 'file_path', 'grade')->get();

        return view('dashboard.user', compact('user', 'upload', 'tasks'));
    }

    public function profile($id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->id != $user->id && auth()->user()->role !== 'admin') {
            abort(403, 'Tidak memiliki izin untuk melihat profil ini.');
        }

        return view('profile', compact('user'));
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->id != $user->id && auth()->user()->role !== 'admin') {
            abort(403, 'Tidak memiliki izin untuk mengedit profil ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('profile.view', $user->id)->with('success', 'Profil berhasil diperbarui.');
    }

    public function showProfile($id)
    {
        $user = User::findOrFail($id); // ambil data user sesuai ID
        $registration = InternshipRegistration::where('user_id', $user->id)->first();
        return view('profile.show', compact('user'));
    }

    public function index()
    {
        $users = User::all();  // Atau bisa dengan pagination, seperti User::paginate(10)
        
        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('profile.show', compact('user'));
    }

    public function destroy($id)
    {
        // Optional: cek jika yang login bukan admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus user.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Data user berhasil dihapus');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user_edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $mentor = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $mentor->id,
            'phone' => 'required|string|max:15',
            'institution' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:20',
            'role' => 'required|string'
        ]);

        $mentor->update($request->only(['name', 'email', 'phone', 'institution', 'major', 'nik']));

        return redirect()->route('admin.dashboard')->with('success', 'Data pembimbing berhasil diperbarui.');
    }
}