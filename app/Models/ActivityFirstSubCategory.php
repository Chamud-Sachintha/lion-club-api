<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFirstSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'main_cat_code',
        'category_name',
        'create_time'
    ];

    public function add_log($info) {
        $map['code'] = $info['firstSubCategoryCode'];
        $map['main_cat_code'] = $info['maincategoryCode'];
        $map['category_name'] = $info['categoryName'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }

    public function query_all() {
        return $this->all();
    }
}
