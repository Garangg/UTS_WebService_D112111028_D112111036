<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Task extends Model 
{
    protected $fillable = [
        'student_id',
        'task_description',
        'deadline',
        'status',
    ];

    public $timestamps = true;

    public function tasks()
    {
        return $this->belongsTo(Student::class);
    }
}
