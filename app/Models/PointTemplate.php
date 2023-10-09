<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'value',
        'create_time'
    ];

    public function add_log($info) {
        $map['code'] = $info['templateCode'];
        $map['value'] = $info['templateValue'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function query_all() {
        return $this->all();
    }

    public function find_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }
}
