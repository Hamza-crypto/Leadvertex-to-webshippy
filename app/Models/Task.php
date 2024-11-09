<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggerTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Task extends Model
{
    use ActivityLoggerTrait;

    protected $fillable = ['title', 'description', 'status', 'assigned_to', 'created_by'];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(TaskUpdate::class);
    }

    public function scopeFilter(Builder $query, $user = null, $status = null)
    {
        if ($user !== null && $user !== -100) {
            $query->where('assigned_to', $user);
        }

        if ($status !== null && $status !== -100) {
            $query->where('status', $status);
        }

        return $query;
    }
}
