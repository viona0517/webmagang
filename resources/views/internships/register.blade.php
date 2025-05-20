@extends('layouts.app')

@section('content')
<style>
    .upload-wrapper {
        background: #fff;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        width: 100%;
        margin: auto;
    }

    .upload-title {
        color: #5E7CC7;
        font-weight: 600;
        margin-bottom: 30px;
        font-size: 20px;
    }

    .file-upload-box {
        background-color: #F7F9FC;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #E0E0E0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .upload-label {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .file-row {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .file-row input[type="url"] {
        flex: 1;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        background-color: #fff;
    }

    .upload-btn {
        background-color: #679CEB;
        color: white;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        transition: 0.2s;
    }

    .upload-btn:hover {
        background-color: #5E7CC7;
    }

    .centered-upload-btn {
        margin-top: 20px;
        width: 100%;
        max-width: 300px;
    }

    .full-center-wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px 15px;
    }
</style>

<div class="full-center-wrapper">
    <div class="upload-wrapper">
        <div class="text-center mb-4">
            <h4 class="fw-semibold">{{ $internship->name }}</h4>
        </div>

        @auth
        @php
            $existingRegistration = \App\Models\InternshipRegistration::where('user_id', auth()->id())
                ->where('internship_id', $internship->id)
                ->first();
        @endphp

        <h5 class="upload-title">📤 Upload Dokumen Persyaratan</h5>

        @if (!$existingRegistration)
            <form action="{{ route('uploads.storeUser') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="internship_id" value="{{ $internship->id }}">

                <div class="file-upload-box">
                    <div class="upload-label">📑 Link CV (GDrive)</div>
                    <div class="file-row">
                        <input type="url" id="cv_link" name="cv_link" placeholder="https://drive.google.com/..." readonly required>
                        <button type="button" onclick="loadPicker(document.getElementById('cv_link'))" class="upload-btn">📁 Pilih</button>
                    </div>
                </div>

                <div class="file-upload-box">
                    <div class="upload-label">📊 Link Rekap Nilai</div>
                    <div class="file-row">
                        <input type="url" id="rekap_link" name="rekap_nilai_link" placeholder="https://drive.google.com/..." readonly required>
                        <button type="button" onclick="loadPicker(document.getElementById('rekap_link'))" class="upload-btn">📁 Pilih</button>
                    </div>
                </div>

                <div class="file-upload-box">
                    <div class="upload-label">📝 Link Surat Persetujuan</div>
                    <div class="file-row">
                        <input type="url" id="surat_link" name="surat_persetujuan_link" placeholder="https://drive.google.com/..." readonly required>
                        <button type="button" onclick="loadPicker(document.getElementById('surat_link'))" class="upload-btn">📁 Pilih</button>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="upload-btn centered-upload-btn">⬆️ Submit Link Dokumen</button>
                </div>
            </form>
        @else
            <div class="text-center text-success">✅ Kamu sudah mengisi dokumen.</div>
        @endif
        @endauth
    </div>
</div>

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notifSound = document.getElementById('notifSound');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '🎉 {{ session('success') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                notifSound.play();
            }
        });
    });
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<audio id="notifSound" src="{{ asset('sounds/notify.mp3') }}" preload="auto"></audio>

{{-- Google Picker --}}
<script type="text/javascript" src="https://apis.google.com/js/api.js"></script>
<script>
    const CLIENT_ID = 'YOUR_CLIENT_ID_HERE';
    const API_KEY = 'YOUR_API_KEY_HERE';
    const SCOPES = ['https://www.googleapis.com/auth/drive.readonly'];

    function loadPicker(callbackTargetInput) {
        gapi.load('auth', { 'callback': () => onAuthApiLoad(callbackTargetInput) });
        gapi.load('picker');
    }

    function onAuthApiLoad(callbackTargetInput) {
        gapi.auth.authorize({
            'client_id': CLIENT_ID,
            'scope': SCOPES,
            'immediate': false
        }, (authResult) => handleAuthResult(authResult, callbackTargetInput));
    }

    function handleAuthResult(authResult, callbackTargetInput) {
        if (authResult && !authResult.error) {
            const picker = new google.picker.PickerBuilder()
                .addView(new google.picker.DocsView()
                .setIncludeFolders(false)
                .setMimeTypes("application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document")
        )
                .setOAuthToken(authResult.access_token)
                .setDeveloperKey(API_KEY)
                .setCallback((data) => pickerCallback(data, callbackTargetInput))
                .build();
            picker.setVisible(true);
        }
    }

    function pickerCallback(data, callbackTargetInput) {
        if (data.action === google.picker.Action.PICKED) {
            const fileUrl = data.docs[0].url;
            callbackTargetInput.value = fileUrl;
        }
    }
</script>
@endsection
