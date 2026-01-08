<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'status',
        'user_id',
        'related_personnel',
        'project',
        'item',
        'department',
        'work',
        'points',
        'release_date',
        'start_date',
        'expected_finish_date',
        'actual_finish_date',
        'output_url',
        'memo',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class);
    }
}
