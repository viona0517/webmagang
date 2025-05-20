<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate([
            'email' => $googleUser->getEmail()
        ], [
            'name' => $googleUser->getName(),
            'password' => bcrypt(uniqid()),
            'nik' => null,
            'institution' => null,
            'major' => null,
            'phone' => null,
            'role' => null,
        ]);

        // Kalau user baru, kirim email verifikasi
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        Auth::login($user);

        if (is_null($user->nik) || is_null($user->phone) || is_null($user->role)) {
            return redirect()->route('verification.notice');
        }

        return redirect('dashboard');
    }

    

}
