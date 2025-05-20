<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Internship;

class TaskController extends Controller
{
    public function index()
    {
        $internships = Internship::with(['users.tasks'])->get(); // Ambil semua magang beserta user + task

        return view('tasks.index', compact('internships'));
    }

    public function create()
    {
        $internships = Internship::all();
        return view('tasks.create', compact('internships'));
    }

    public function grade(Request $request, Task $task)
    {
        $userId = $request->input('user_id');
        $grade = $request->input('grade');

        // Simpan nilai ke pivot table (task_user)
        $task->users()->updateExistingPivot($userId, ['grade' => $grade]);

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'deadline'      => 'required|date',
        ]);

        // 1. Buat task dulu
        $task = Task::create([
            'internship_id' => $data['internship_id'],
            'title'         => $data['title'],
            'description'   => $data['description'],
            'deadline'      => $data['deadline'],
            'user_id'       => Auth::id(), // Menambahkan user_id default
        ]);

        // 2. Ambil semua user yang diterima
        $users = User::whereHas('internships', function ($query) use ($data) {
            $query->where('internships.id', $data['internship_id'])
                ->where('internship_registrations.status_lowongan', 'diterima');
        })->get();

        // 3. Attach semua user ke task lewat pivot
        $task->users()->attach(
            $users->pluck('id')->toArray(),
            ['status' => 'pending']
        );

        return redirect()->route('pembimbing.dashboard')->with('success', 'Tugas berhasil dibuat untuk semua peserta!');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|mimes:pdf,docx,jpg,png|max:2048'
        ]);

        $task->title = $request->title;
        $task->description = $request->description;

        if ($request->hasFile('file')) {
            if ($task->file_path) {
                Storage::disk('public')->delete($task->file_path);
            }

            $filePath = $request->file('file')->store('uploads', 'public');
            $task->file_path = $filePath;
        }

        $task->save();

        return redirect()->route('pembimbing.dashboard')->with('success', 'Tugas berhasil diperbarui!');
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.edit', compact('task'));
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->file_path) {
            Storage::disk('public')->delete($task->file_path);
        }

        $task->delete();

        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }

    public function uploadForm(Task $task)
    {
        $user = Auth::user();

        if ($user->role !== 'user' || !$user->tasks->contains($task->id)) {
            abort(403, 'Tidak diizinkan.');
        }
    
        return view('tasks.upload', compact('task'));
    }

    public function uploadFile(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($user->role !== 'user' || !$user->tasks()->where('task_id', $task->id)->exists()) {
            abort(403, 'Tidak diizinkan.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip,png,jpg|max:2048',
            'status' => 'required|in:in_progress,completed',
        ]);

        if ($request->hasFile('file')) {
            $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs('tasks', $filename, 'public');

            // Simpan ke pivot table
            Auth::user()->tasks()->updateExistingPivot($task->id, [
                'file_path' => 'tasks/' . $filename,
                'status' => $request->status,
            ]);
        }

        return redirect()->route('user.dashboard')->with('success', 'File berhasil diupload.');
    }


    public function showFile($filename)
    {
        $filePath = storage_path("app/public/tasks/{$filename}"); // ganti uploads -> tasks

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function assignTaskToInternship(Request $request)
    {
        $data = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'deadline'      => 'required|date',
        ]);

        // Buat 1 task dulu
        $task = Task::create([
            'internship_id' => $data['internship_id'],
            'title'         => $data['title'],
            'description'   => $data['description'],
            'deadline'      => $data['deadline'],
            'user_id'       => Auth::id(),
        ]);

        // Ambil semua user yang diterima
        $users = User::whereHas('internships', function ($query) use ($data) {
            $query->where('internships.id', $data['internship_id'])
                ->where('internship_registrations.status_lowongan', 'diterima');
        })->get();

        // Siapkan data untuk attach ke pivot
        $attachData = [];
        foreach ($users as $user) {
            $attachData[$user->id] = [
                'status' => 'pending',
                'file_path' => null,
                'grade' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $task->users()->attach($attachData);
        
        return redirect()
            ->route('pembimbing.dashboard')
            ->with('success', 'Tugas berhasil dibuat dan dibagikan ke semua peserta!');
    }


}
