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

    public function update_first_category_by_code($firstCatInfo) {
        $map['code'] = $firstCatInfo['firstCategoryCode'];
        $map1['main_cat_code'] = $firstCatInfo['mainCatCode'];
        $map1['category_name'] = $firstCatInfo['categoryNmae'];

        return $this->where($map)->update($map1);
    }

    public function get_info_by_main_cat_code($mainCategoryCode) {
        $map['main_cat_code'] = $mainCategoryCode;

        return $this->where($map)->get();
    }

    public function delete_category_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->delete();
    }

    public function query_all() {
        return $this->all();
    }
}
