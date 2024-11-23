<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoList extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'description'];

    // Relasi dengan Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi dengan Task
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'setting_project_user', 'project_id', 'user_id');
    }
}
