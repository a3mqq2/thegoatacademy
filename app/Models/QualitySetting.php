<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualitySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'red_threshold', 'yellow_threshold', 'green_threshold',
    ];
}
