<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Memo extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_name',
        'content',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
