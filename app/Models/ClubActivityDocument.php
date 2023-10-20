<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivityDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_code',
        'document_name',
        'create_time'
    ];

    public function add_log($info) {
        $map['activity_code'] = $info['activityCode'];
        $map['document_name'] = $info['document'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function query_find_docs($activityCode) {
        $map['activity_code'] = $activityCode;

        return $this->where($map)->get();
    }
}
