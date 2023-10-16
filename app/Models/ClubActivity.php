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
        'cost',
        'benificiaries',
        'member_count',
        'status',
        'create_time'
    ];

    public function add_log($activityInfo) {
        $map['activity_code'] = $activityInfo['activityCode'];
        $map['club_code'] = $activityInfo['clubCode'];
        $map['cost'] = $activityInfo['cost'];
        $map['benificiaries'] = $activityInfo['benificiaries'];
        $map['member_count'] = $activityInfo['memberCount'];
        $map['create_time'] = $activityInfo['createTime'];
        $map['status'] = 0;

        return $this->create($map);
    }

    public function query_all() {
        return $this->all();
    }
}
