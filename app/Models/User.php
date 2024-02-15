<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;


    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'is_visible',
    ];

    protected $hidden = [
        'password',
    ];

    public const ROLES = [
        'ADMIN' => 1,
        'ADVISOR' => 2,
        'AGENCY' => 3,
        'CREATIVE' => 4,
        'RECRUITER' => 5,
    ];

    public const STATUSES = [
        'PENDING' => 0,
        'ACTIVE' => 1,
        'INACTIVE' => 2,
    ];

    public function getFullNameAttribute()
    {
        $fullName = trim($this->first_name . ' ' . $this->last_name);

        return $fullName !== '' ? $fullName : $this->username;
    }

    public function getRoleAttribute($value)
    {
        switch ($value) {
            case User::ROLES['ADMIN']:
                return 'admin';
            case User::ROLES['ADVISOR']:
                return 'advisor';
            case User::ROLES['AGENCY']:
                return 'agency';
            case User::ROLES['CREATIVE']:
                return 'creative';
            case User::ROLES['RECRUITER']:
                return 'recruiter';
            default:
                return null;
        }
    }

    public function setRoleAttribute($value)
    {
        switch ($value) {
            case 'admin':
                $this->attributes['role'] = User::ROLES['ADMIN'];
                break;
            case 'advisor':
                $this->attributes['role'] = User::ROLES['ADVISOR'];
                break;
            case 'agency':
                $this->attributes['role'] = User::ROLES['AGENCY'];
                break;
            case 'recruiter':
                $this->attributes['role'] = User::ROLES['RECRUITER'];
                break;
            default:
                $this->attributes['role'] = User::ROLES['CREATIVE'];
                break;
        }
    }

    public function getStatusAttribute($value)
    {
        switch ($value) {
            case User::STATUSES['PENDING']:
                return 'pending';
            case User::STATUSES['ACTIVE']:
                return 'active';
            case User::STATUSES['INACTIVE']:
                return 'inactive';

            default:
                return null;
        }
    }

    public function setStatusAttribute($value)
    {
        switch ($value) {
            case 'active':
                $this->attributes['status'] = User::STATUSES['ACTIVE'];
                break;
            case 'inactive':
                $this->attributes['status'] = User::STATUSES['INACTIVE'];
                break;
            default:
                $this->attributes['status'] = User::STATUSES['PENDING'];
                break;
        }
    }
}