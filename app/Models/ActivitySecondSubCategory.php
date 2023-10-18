<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySecondSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'first_cat_code',
        'category_name',
        'create_time'
    ];

    public function add_log($info) {
        $map['code'] = $info['secondCategoryCode'];
        $map['first_cat_code'] = $info['firstCategoryCode'];
        $map['category_name'] = $info['categoryName'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_first_cat_code($firstCategoryCode) {
        $map['first_cat_code'] = $firstCategoryCode;

        return $this->where($map)->get();
    }

    public function find_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }

    public function query_all() {
        return $this->all();
    }
}
