<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFirstSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'main_category_code',
        'activity_name',
        'create_time'
    ];

    public function add_log() {

    }

    public function query_all() {
        
    }
}
