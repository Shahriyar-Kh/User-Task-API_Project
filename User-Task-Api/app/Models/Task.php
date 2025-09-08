<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'user_id',     // assigned user
        'created_by',  // admin who created the task
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * 🔹 Task belongs to the assigned user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Unknown User',
            'email' => 'unknown@example.com',
        ]);
    }

    /**
     * 🔹 Task also belongs to the admin who created it
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'name' => 'Unknown Admin',
        ]);
    }
}