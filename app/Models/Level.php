<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['name'];


    public function users()
    {
        return $this->hasMany(User::class, 'level_id', 'id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'level_id', 'id');
    }
}
