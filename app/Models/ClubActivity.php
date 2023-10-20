<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_code',
        'club_code',
        'type',
        'status',
        'create_time'
    ];

    public function add_log($activityInfo) {
        $map['activity_code'] = $activityInfo['activityCode'];
        $map['club_code'] = $activityInfo['clubCode'];
        $map['type'] = $activityInfo['type'];
        // $map['value'] = $activityInfo['value'];
        $map['create_time'] = $activityInfo['createTime'];
        $map['status'] = 0;

        return $this->create($map);
    }

    public function query_find_by_code($activityCode) {
        $map['activity_code'] = $activityCode;

        return $this->where($map)->first();
    }

    public function query_all() {
        return $this->all();
    }
}
