<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_code',
        'context_user_code',
        'create_time'
    ];

    public function add_log($info) {
        $map['region_code'] = $info['reCode'];
        $map['context_user_code'] = $info['contextUserCode'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_code($code) {
        $map['region_code'] = $code;

        return $this->where($map)->first();
    }

    public function query_all() {
        return $this->all();
    }

    public function get_region_list($info) {
        $map['context_user_code'] = $info['contextUserCode'];

        return $this->where($map)->get();
    }
}
