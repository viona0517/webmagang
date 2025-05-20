@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full max-w-3xl bg-white p-8 shadow-lg rounded-lg">
        <h2 class="text-center text-2xl font-bold mb-6">Verifikasi Email</h2>

        <div class="text-center mb-4">
            <p class="text-lg">Kami telah mengirimkan email verifikasi ke alamat email Anda. Silakan cek inbox atau folder spam Anda.</p>
        </div>

        <div class="mb-4">
            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-blue-700 text-white p-3 rounded hover:bg-blue-800">
                    Kirim Ulang Verifikasi
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <p>Sudah memverifikasi? <a href="{{ route('login') }}" class="text-red-600">Login Sekarang</a></p>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">Jika Anda tidak menerima email verifikasi, pastikan untuk memeriksa folder spam atau coba kirim ulang email verifikasi.</p>
        </div>
    </div>
</div>
@endsection