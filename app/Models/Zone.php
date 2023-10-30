<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\returnSelf;

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

    public function update_zone_by_code($zoneInfo) {
        $map['zone_code'] = $zoneInfo['zoneCode'];
        $map1['re_code'] = $zoneInfo['reCode'];

        return $this->where($map)->update($map1);
    }

    public function find_by_re_code($reCode) {
        $map['re_code'] = $reCode;

        return $this->where($map)->get();
    }

    public function delete_zone_by_code($zoneCode) {
        $map['zone_code'] = $zoneCode;
        
        return $this->where($map)->delete();
    }

    public function query_all() {
        return $this->all();
    }
}
