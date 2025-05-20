<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Internship;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware untuk memastikan hanya admin yang bisa mengakses
        $this->middleware(['auth', 'admin']);
    }

    public function profile()
    {
        $user = Auth::user(); // Ambil data user yang sedang login
        return view('profile.show', compact('user'));
    }

    public function arsip()
    {
    // Ambil semua program magang dengan relasi registrations dan user dari registrasi
    $programs = Internship::with(['registrations.user'])->get();

    return view('admin.arsip', compact('programs'));
    }
    
    public function index()
    {
        // Ambil data internships beserta relasi applicants
        $internships = Internship::with('applicants')->get();

        // Menghitung jumlah pendaftar yang sudah terverifikasi email
        $jumlahPendaftar = User::where('role', 'user')
                            ->whereNotNull('email_verified_at') // Pastikan email sudah terverifikasi
                            ->count();

        // Menghitung jumlah pembimbing yang sudah terverifikasi email
        $jumlahPembimbing = User::where('role', 'pembimbing')
                                ->whereNotNull('email_verified_at') // Pastikan email sudah terverifikasi
                                ->count();

        // Menghitung jumlah program magang
        $jumlahProgram = Internship::count();

     // Ambil semua user dengan role 'user' yang sudah verifikasi email
    $users = User::where('role', 'user')
                ->whereNotNull('email_verified_at')
                ->get();

    // Ambil semua user dengan role 'pembimbing' yang sudah verifikasi email
    $mentors = User::where('role', 'pembimbing')
                ->whereNotNull('email_verified_at')
                ->get();

        // Kirim data ke view
        return view('admin.dashboard', compact(
            'internships',
            'users',
            'jumlahPendaftar',
            'jumlahPembimbing',
            'jumlahProgram',
            'mentors'
        ));
    }

}