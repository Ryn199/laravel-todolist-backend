<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'todo_list_id',
        'title',
        'description',
        'status',
        'start_time',
        'end_time',
    ];

    public function todoList()
    {
        return $this->belongsTo(TodoList::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'setting_task_user');
    }
    
}
