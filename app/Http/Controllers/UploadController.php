<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternshipRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function storeUser(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'cv_link' => 'nullable|url|max:255',
            'rekap_nilai_link' => 'nullable|url|max:255',
            'surat_persetujuan_link' => 'nullable|url|max:255',
            'jurusan' => 'nullable|string|max:255', // validasi jurusan jika dikirim dari form
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Anda belum login.');
        }

        // Cek apakah user sudah mendaftar ke internship ini
        $existing = InternshipRegistration::where('user_id', $user->id)
                    ->where('internship_id', $request->internship_id)
                    ->first();

        if ($existing) {
            return back()->with('warning', 'Anda sudah mendaftar untuk program ini.');
        }

        InternshipRegistration::create([
            'user_id' => $user->id,
            'internship_id' => $request->internship_id,
            'name' => $user->name,
            'email' => $user->email,
            'nik' => $user->nik ?? '', // pakai default jika null
            'university' => $user->institution ?? '',
            'jurusan' => $user->major ?? '',
            'phone' => $user->phone ?? '',
            'cv' => $request->cv_link,
            'rekap_nilai' => $request->rekap_nilai_link,
            'surat_persetujuan' => $request->surat_persetujuan_link,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Pendaftaran berhasil dikirim.');
    }
}
