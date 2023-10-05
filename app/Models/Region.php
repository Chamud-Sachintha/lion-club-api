<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_code',
        're_chair_person_code',
        'create_time'
    ];

    public function add_log($info) {
        $map['region_code'] = $info['reCode'];
        $map['re_chair_person_code'] = $info['regionChairPersonCode'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }
}
