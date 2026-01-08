<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'title',
        'status',
        'confirm',
        'deadline',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_finish',
        'memo',
    ];

    public function memos()
    {
        return $this->hasMany(Memo::class);
    }
}
