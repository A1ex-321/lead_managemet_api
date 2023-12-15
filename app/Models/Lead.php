<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    protected $table = 'lead';

    // protected $fillable = [
    //     'comment','name','phone','email','platform','address','websiteDetails','projectDetails','interestedServices','servicesTaken','group','tags','category','is_shedule','date_shedule'
    // ];
    protected $fillable = [
        'name','phone',
    ];
    public function comments()
    {
        return $this->hasMany(Comments::class, 'lead_id');
    }
}
