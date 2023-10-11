<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_code',
        're_code',
        'create_time'
    ];

    public function add_log($info) {
        $map['zone_code'] = $info['zoneCode'];
        // $map['zn_chair_person_code'] = $info['chairPersonCode'];
        $map['re_code'] = $info['regionCode'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_zone_code($code) {
        $map['zone_code'] = $code;

        return $this->where($map)->first();
    }

    public function query_all() {
        return $this->all();
    }
}
