<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'setting_project_user', 'project_id', 'user_id');
    }

    public function todoLists()
    {
        return $this->hasMany(TodoList::class);
    }
}
