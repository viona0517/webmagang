<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'file_path', 'user_id', 'deadline', 'status', 'grade', 'internship_id'];

    // Relasi ke user
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'file_path', 'grade')
            ->withTimestamps();
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

}
