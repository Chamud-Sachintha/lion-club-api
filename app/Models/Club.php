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
        'create_time'
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

    public function query_all() {
        return $this->all();
    }
}
