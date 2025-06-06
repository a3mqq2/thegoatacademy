<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'password',
        'age',
        'gender',
        'nationality',
        'video',
        'notes',
        'phone',
        'avatar',
        'level_id',
        'cost_per_hour'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'id');
    }


    public function courses() 
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)->withTimestamps();
    }


    public function levels()
    {
        return $this->belongsToMany(Level::class)->withTimestamps();
    }

    public function shifts()
    {
        return $this->hasMany(UserShift::class,'user_id','id'); 
    }

}
