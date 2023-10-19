<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivityImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_code',
        'image',
        'create_time'
    ];

    public function add_log($imageInfo) {
        $map['activity_code'] = $imageInfo['activityCode'];
        $map['image'] = $imageInfo['image'];
        $map['create_time'] = $imageInfo['createTime'];

        return $this->create($map);
    }
}
