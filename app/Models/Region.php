<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_code',
        're_chair_person_code',
        'create_time'
    ];

    public function add_log() {
        
    }
}
