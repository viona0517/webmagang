@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full max-w-3xl bg-white p-8 shadow-lg rounded-lg">
        <h2 class="text-center text-2xl font-bold mb-6">Register</h2>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <form action="{{ Auth::check() ? route('profile.complete') : route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="institution" class="block font-medium">Instansi Pendidikan</label>
                    <input type="text" class="w-full p-2 border rounded" id="institution" name="institution" placeholder="Tulis Instansi Pendidikan Anda" required>
                </div>
            
                <div>
                    <label for="major" class="block font-medium">Jurusan</label>
                    <input type="text" class="w-full p-2 border rounded" id="major" name="major" placeholder="Tulis Jurusan Anda" required>
                </div>
            </div>

           <div>
                <label for="name" class="block font-medium">Nama Lengkap</label>
                @if(Auth::check())
                    <input type="text" class="w-full p-2 border rounded bg-gray-100 cursor-not-allowed"
                        id="name" name="name" value="{{ old('name', Auth::user()->name) }}" readonly>
                @else
                    <input type="text" class="w-full p-2 border rounded" id="name" name="name"
                        value="{{ old('name') }}" required>
                @endif
            </div>


            <div>
                <label for="nik" class="block font-medium">Nomor Induk Kependudukan (NIK)</label>
                <input type="text"
                    class="w-full p-2 border rounded"
                    id="nik"
                    name="nik"
                    value="{{ old('nik') }}"
                    maxlength="16"
                    required>
                <p id="nikError" class="text-red-600 text-sm mt-1 hidden">NIK harus terdiri dari 16 digit angka</p>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block font-medium">Email</label>
                    @if(Auth::check())
                        <input type="email" class="w-full p-2 border rounded bg-gray-100 cursor-not-allowed"
                            id="email" name="email" value="{{ old('email', Auth::user()->email) }}" readonly>
                    @else
                        <input type="email" class="w-full p-2 border rounded" id="email" name="email"
                            value="{{ old('email') }}" required>
                    @endif
                </div>
               

                <div>
                    <label for="phone" class="block font-medium">Nomor HP</label>
                    <input type="text" class="w-full p-2 border rounded" id="phone" name="phone" value="{{ old('phone') }}" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if(!Auth::check())
                <div>
                    <label for="password" class="block font-medium">Password</label>
                    <div class="relative">
                        <input type="password" class="w-full p-2 border rounded pr-10"
                            id="password" name="password" required>
                        <button type="button" class="absolute right-3 top-3"
                            onclick="togglePassword('password', 'togglePasswordIcon')">
                            <i id="togglePasswordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block font-medium">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" class="w-full p-2 border rounded pr-10"
                            id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="absolute right-3 top-3"
                            onclick="togglePassword('password_confirmation', 'toggleConfirmPasswordIcon')">
                            <i id="toggleConfirmPasswordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <div>
                <label for="role" class="block font-medium">Peran</label>
                <select class="w-full p-2 border rounded" id="role" name="role" required>
                    <option value="">Pilih Peran</option>
                    <option value="user">User</option>
                    <option value="pembimbing">Pembimbing</option>
                </select>
            </div>

          @if(!Auth::check() || Auth::user()->provider !== 'google')
                <div class="text-center">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                </div>
            @endif


           @if(Auth::check())
                <button type="submit" class="w-full bg-green-700 text-white p-3 rounded hover:bg-green-800">Lengkapi Profil</button>
            @else
                <button type="submit" class="w-full bg-blue-700 text-white p-3 rounded hover:bg-blue-800">Daftar</button>
            @endif

            <div class="mt-4 text-center">
                <p class="mb-2">atau</p>
                <a href="{{ route('google.login') }}"
                    class="w-full flex items-center justify-center gap-2 border border-gray-400 text-gray-700 bg-white hover:bg-gray-100 font-medium py-2 px-4 rounded-lg shadow-sm">
                    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="w-5 h-5">
                    <span>Sign Up with Google</span>
                </a>
            </div>
        <p class="mt-3 text-center">Sudah punya akun? <a href="{{ route('login') }}" class="text-red-600">Login</a></p>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
    function togglePassword(fieldId, iconId) {
        let field = document.getElementById(fieldId);
        let icon = document.getElementById(iconId);
        if (field.type === "password") {
            field.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            field.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

<script>
    const nikInput = document.getElementById('nik');
    const nikError = document.getElementById('nikError');

    nikInput.addEventListener('input', function () {
        const nik = nikInput.value;
        const isValid = /^\d{16}$/.test(nik);

        if (nik.length === 0 || isValid) {
            nikError.classList.add('hidden');
            nikInput.classList.remove('border-red-500');
            nikInput.classList.add('border-gray-300');
        } else {
            nikError.classList.remove('hidden');
            nikInput.classList.remove('border-gray-300');
            nikInput.classList.add('border-red-500');
        }
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection