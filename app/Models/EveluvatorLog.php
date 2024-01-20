<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EveluvatorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'activity',
        'club_code',
        'comment',
        'requested_range',
        'requested_points',
        'claimed_range',
        'claimed_points',
        'eveluvated_date',
        'status',
        'create_time'
    ];

    public function add_log($info) {
        $map['name'] = $info['name'];
        $map['activity'] = $info['activityCode'];
        $map['club_code'] = $info['clubCode'];
        $map['comment'] = $info['comment'];
        $map['requested_range'] = $info['requestedRange'];
        $map['requested_points'] = $info['requestedPoints'];
        $map['claimed_range'] = $info['claimedRange'];
        $map['claimed_points'] = $info['claimedPoints'];
        $map['eveluvated_date'] = $info['eveluvatedDate'];
        $map['status'] = $info['status'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function get_all() {
        return $this->all();
    }
}
