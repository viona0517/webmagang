<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:20|unique:users,nik',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:15',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:User,Pembimbing',
            'g-recaptcha-response' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Silakan periksa kembali isian Anda.');
        }

        // Validasi Google reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
        ]);

        if (!$response->json()['success']) {
            return redirect()->back()
                ->withErrors(['g-recaptcha-response' => 'Verifikasi Captcha gagal.'])
                ->withInput();
        }

        // Simpan user ke database
        $user = User::create([
            'institution' => $request->institution,
            'major' => $request->major,
            'name' => $request->name,
            'nik' => $request->nik,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => strtolower($request->role),
        ]);

        // Login user langsung agar bisa akses middleware `auth`
        Auth::login($user);

        // Kirim email verifikasi
        $user->sendEmailVerificationNotification();

        // Arahkan ke halaman pemberitahuan verifikasi email
        return redirect()->route('verification.notice');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'g-recaptcha-response' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validasi Google reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
        ]);

        if (!$response->json()['success']) {
            return redirect()->back()->withErrors(['g-recaptcha-response' => 'Verifikasi Captcha gagal.'])->withInput();
        }

        // Proses login
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek apakah email sudah diverifikasi
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Email belum diverifikasi. Silakan cek email Anda.',
                ])->withInput();
            }

            // Redirect berdasarkan role
            switch ($user->role) {
                case 'admin':
                    return redirect('/admin/dashboard')->with('success', 'Login berhasil!');
                case 'pembimbing':
                    return redirect('/pembimbing/dashboard')->with('success', 'Login berhasil!');
                default:
                    return redirect('/dashboard')->with('success', 'Login berhasil!');
            }
        }

        return redirect()->back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout berhasil.');
    }

    public function profile()
    {
        $user = Auth::user(); // Ambil data user yang sedang login
        return view('profile.profile', compact('user'));
    }

    public function loginPage()
    {
        return view('auth.login');
    }


    public function showCompleteForm()
    {
        return view('auth.register'); // View berisi form seperti yang kamu punya
    }

    public function submitCompleteProfile(Request $request)
    {
        $request->validate([
            'institution' => 'required',
            'major' => 'required',
            'nik' => 'required|digits:16',
            'phone' => 'required',
            'role' => 'required',
        ]);

        $user = Auth::user();
        $user->institution = $request->institution;
        $user->major = $request->major;
        $user->nik = $request->nik;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->save();

        return redirect()->route('home')->with('status', 'Data berhasil dilengkapi.');
    }

    
}
