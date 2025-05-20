@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-8 px-4">
    <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-blue-600">User Dashboard</h2>
        <p class="text-gray-600">Selamat datang, {{ Auth::user()->name }}!</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left border border-gray-300 shadow-md rounded-lg">
            <thead class="bg-blue-500 text-white">
                <tr>
                    <th class="px-4 py-2">Judul Task</th>
                    <th class="px-4 py-2">Deskripsi</th>
                    <th class="px-4 py-2 text-center">File</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Deadline</th> 
                    <th class="px-4 py-2 text-center">Nilai</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-4 py-2">{{ $task->title }}</td>
                            <td class="px-4 py-2">{{ $task->description }}</td>
                            <td class="px-4 py-2 text-center">
                                @php
                                    $pivot = $task->users->firstWhere('id', Auth::id())?->pivot;
                                @endphp
                                @if(Auth::user()->role == 'user' && $pivot && strtolower($pivot->status) != 'completed')
                                    <a href="{{ route('tasks.upload', $task) }}" 
                                        class="bg-blue-500 text-white px-2 py-1 rounded">
                                        {{ $pivot->file_path ? 'Ganti File' : 'Upload File' }}
                                    </a>
                                @endif

                                @if ($pivot && $pivot->file_path)
                                    <a href="{{ asset('storage/' . $pivot->file_path) }}" 
                                        class="text-blue-600 underline ml-2" target="_blank">
                                        Lihat File
                                    </a>
                                @else
                                    <span class="text-red-500">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    {{ $pivot->status === 'completed' ? 'bg-green-500 text-white' : 'bg-yellow-400 text-gray-800' }}">
                                    {{ ucfirst($pivot->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                @php
                                    $deadline = \Carbon\Carbon::parse($task->deadline);
                                    $now = \Carbon\Carbon::now();
                                    $isLate = $now->gt($deadline);
                                @endphp
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    {{ $isLate ? 'bg-red-500 text-white' : 'bg-blue-500 text-white' }}">
                                    {{ $deadline->format('d M Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                            @if($pivot && $pivot->grade)
                                <span class="font-bold text-green-600">{{ $pivot->grade }}</span>
                            @else
                                <span class="text-gray-400 italic">Belum dinilai</span>
                            @endif
                        </td>

                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-4">
                            Tidak ada tugas yang tersedia.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
