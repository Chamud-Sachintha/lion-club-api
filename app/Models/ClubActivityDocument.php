<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivityDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_code',
        'document_code',
        'create_time'
    ];

    public function add_log($info) {
        $map['activity_code'] = $info['activityCode'];
        $map['document_code'] = $info['document'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }
}
