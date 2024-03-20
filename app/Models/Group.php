<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'tags',
    ];

    protected $casts = [
        'category' => 'array',
        'tags' => 'array',
    ];
}
