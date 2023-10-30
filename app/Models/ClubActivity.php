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
        'creator', // 1- club user 2 - context user
        'create_time'
    ];

    public function add_log($activityInfo) {
        $map['activity_code'] = $activityInfo['activityCode'];
        $map['club_code'] = $activityInfo['clubCode'];
        $map['type'] = $activityInfo['type'];
        // $map['value'] = $activityInfo['value'];
        $map['create_time'] = $activityInfo['createTime'];
        $map['status'] = 0;
        $map['creator'] = $activityInfo['creator'];

        return $this->create($map);
    }

    public function query_find_by_code($activityCode) {
        $map['activity_code'] = $activityCode;

        return $this->where($map)->first();
    }

    public function get_activity_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;

        return $this->where($map)->count();
    }

    public function update_status_by_id($info) {
        $map['id'] = $info['clubActivityCode'];
        $map1['status'] = $info['status'];

        return $this->where($map)->update($map1);
    }

    public function find_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->first();
    }

    public function get_list_by_creator($code) {
        $map['creator'] = $code;

        return $this->where($map)->get();
    }

    public function find_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;

        return $this->where($map)->get();
    }

    public function query_all() {
        return $this->all();
    }
}
