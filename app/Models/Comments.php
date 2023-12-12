<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;
    protected $table = 'comments';

    protected $fillable = [
        'lead_id', 'comment', 'postedOn',
    ];
    public function leads()
    {
        return $this->belongsTo(Lead::class, 'id');
    }
}
