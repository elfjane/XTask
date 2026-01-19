<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $review_status
 * @property string $status
 * @property int $id
 * @property string $actual_finish_date
 * @property \Carbon\Carbon $approved_at
 * @property \Carbon\Carbon $reviewed_at
 * @property \Carbon\Carbon $failed_at
 */
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
        'sort_order',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'failed_at',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'start_date' => 'datetime',
        'expected_finish_date' => 'datetime',
        'actual_finish_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'failed_at' => 'datetime',
        'points' => 'float',
        'level' => 'integer',
        'sort_order' => 'integer',
        'user_id' => 'integer',
        'reviewed_by' => 'integer',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class);
    }

    public function latestRemark()
    {
        return $this->hasOne(Remark::class)->latestOfMany();
    }
}
