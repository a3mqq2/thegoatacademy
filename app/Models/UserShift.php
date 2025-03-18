<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShift extends Model
{
    // Optional: if your table name is not the plural of the model name
    protected $table = 'user_shifts';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'user_id', 'day', 'start_time', 'end_time'
    ];

    // Define the relationship: a shift belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
