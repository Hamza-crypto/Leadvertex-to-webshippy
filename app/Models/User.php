<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Impersonate;


    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'remember_token',
    ];

    protected $hidden = [
        'password',
    ];

    public function canImpersonate()
    {
        return $this->role == 'admin';
    }


    public static function getRoles()
    {
        return [
            'admin' => 'Admin',
            'operational_manager' => 'Operational Manager',
            'marketing_manager' => 'Marketing Manager',
        ];
    }

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
