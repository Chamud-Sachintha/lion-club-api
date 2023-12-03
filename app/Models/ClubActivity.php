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
        'ext_value',
        'date_of_activity',
        'comment',
        'create_time',
        'aditional_info'
    ];

    public function add_log($activityInfo) {
        $map['activity_code'] = $activityInfo['activityCode'];
        $map['club_code'] = $activityInfo['clubCode'];
        $map['type'] = $activityInfo['type'];
        // $map['value'] = $activityInfo['value'];
        $map['create_time'] = $activityInfo['createTime'];
        $map['status'] = 0;
        $map['creator'] = $activityInfo['creator'];
        $map['ext_value'] = $activityInfo['extValue'];
        $map['date_of_activity'] = $activityInfo['dateOfActivity'];
        $map['aditional_info'] = $activityInfo['aditionalInfo'];

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
        $map1['comment'] = $info['comment'];

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
        return $this->orderBy('create_time', 'desc')->get();
    }

    public function get_activity_count() {
        return $this->all()->count();
    }

    public function get_approved_acivity_count() {
        $map['status'] = 1;

        return $this->where($map)->count();
    }

    public function get_pending_acivity_count() {
        $map['status'] = 0;

        return $this->where($map)->count();
    }

    public function get_hold_acivity_count() {
        $map['status'] = 3;

        return $this->where($map)->count();
    }

    public function get_approved_aith_corrections_acivity_count() {
        $map['status'] = 4;

        return $this->where($map)->count();
    }

    public function get_rejected_activity_count() {
        $map['status'] = 2;

        return $this->where($map)->count();
    }

    public function get_approved_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;
        $map['status'] = 1;

        return $this->where($map)->count();
    }

    public function get_approved_with_corrections_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;
        $map['status'] = 4;

        return $this->where($map)->count();
    }

    public function get_rejected_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;
        $map['status'] = 2;

        return $this->where($map)->count();
    }

    public function get_pending_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;
        $map['status'] = 0;

        return $this->where($map)->count();
    }

    public function get_hold_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;
        $map['status'] = 3;

        return $this->where($map)->count();
    }

    public function get_total_count_by_club_code($clubCode) {
        $map['club_code'] = $clubCode;

        return $this->where($map)->count();
    }

    public function get_approved_list() {
        $map['status'] = 1;

        return $this->where($map)->get();
    }
}
