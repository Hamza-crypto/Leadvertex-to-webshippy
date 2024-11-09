<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggerTrait;

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
}
