<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivtyPointReserve extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_code',
        'club_code',
        'points',
        'time'
    ];

    public function add_log($info) {
        $map['club_activity_code'] = $info['clubActivityCode'];
        $map['club_code'] = $info['clubCode'];
        $map['points'] = $info['points'];
        $map['time'] = $info['createTime'];

        return $this->create($map);
    }

    public function get_points__by_club_code($clubCode) {
        $map['club_code'] = $clubCode;

        return $this->where($map)->sum('points');
    }

    public function get_points_by_activity_and_club($activityCode, $clubCode) {
        $map['club_activity_code'] = $activityCode;
        $map['club_code'] = $clubCode;

        return $this->where($map)->first();
    }

    public function get_ordered_list() {
        return $this->orderBy("points", "asc")->get();
    }
}
