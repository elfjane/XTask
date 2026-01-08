<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Remark extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_name',
        'content',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
