<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawnReason extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
