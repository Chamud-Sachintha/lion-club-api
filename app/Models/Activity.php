<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'main_cat_code',
        'first_cat_code',
        'second_cat_code',
        'activity_name',
        'authorized_user',
        'point_template_code',
        'doc_code',
        'create_time'
    ];

    public function add_log($activityInfo) {
        // dd($activityInfo);
        $map['code'] = $activityInfo['activityCode'];
        $map['main_cat_code'] = $activityInfo['mainCategoryCode'];
        $map['first_cat_code'] = $activityInfo['firstCategoryCode'];
        $map['second_cat_code'] = $activityInfo['secondCategoryCode'];
        $map['activity_name'] = $activityInfo['activityName'];
        $map['authorized_user'] = $activityInfo['authUser'];
        $map['point_template_code'] = $activityInfo['templateCode'];
        $map['doc_code'] = json_encode($activityInfo['docCode']);
        $map['create_time'] = $activityInfo['createTime'];

        return $this->create($map);
    }

    public function find_by_codes($info) {
        $map['first_cat_code'] = $info['firstCategoryCode'];
        $map['main_cat_code'] = $info['mainCategoryCode'];
        $map['second_cat_code'] = $info['secondCategoryCode'];

        return $this->where($map)->get();
    }

    public function query_all() {
        return $this->all();
    }

    public function query_find($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }
}
