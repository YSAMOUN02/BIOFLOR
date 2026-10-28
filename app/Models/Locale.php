<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
