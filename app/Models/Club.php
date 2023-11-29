<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_code',
        'zone_code',
        'create_time',
        'total_points'
    ];

    public function add_log($info) {
        $map['club_code'] = $info['clubCode'];
        $map['zone_code'] = $info['zoneCode'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_club_code($code) {
        $map['club_code'] = $code;

        return $this->where($map)->first();
    }

    public function update_club_by_code($clubInfo) {
        $map['club_code'] = $clubInfo['clubCode'];
        $map1['zone_code'] = $clubInfo['zoneCode'];

        return $this->where($map)->update($map1);
    }

    public function get_club_count() {
        return $this->count();
    }

    public function query_all() {
        return $this->all();
    }

    public function delete_club_by_code($clubCode) {
        $map['club_code'] = $clubCode;

        return $this->where($map)->delete();
    }

    public function getClubListByZoneCode($zoneCode) {
        $map['zone_code'] = $zoneCode;

        return $this->where($map)->get();
    }

    public function update_club_points($clubInfo) {
        $map['club_code'] = $clubInfo['clubCode'];
        $map1['total_points'] = $clubInfo['updatedPoints'];

        $this->where($map)->update($map1);
    }

    public function get_club_list_by_points_order() {
        return $this->orderBy("total_points", "desc")->get();
    }
}
