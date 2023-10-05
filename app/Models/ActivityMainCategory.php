<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityMainCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'create_time'
    ];

    public function add_log($info) {
        $map['code'] = $info['mainCategoryCode'];
        $map['name'] = $info['mainCategoryName'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function query_all() {
        return $this->all();
    }
}
