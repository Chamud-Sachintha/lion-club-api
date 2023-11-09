<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ClubUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'club_code',
        'name',
        'email',
        'password',
        'token',
        'login_time',
        'create_time',
        'flag'
    ];

    public function add_log($userInfo) {
        $map['code'] = $userInfo['code'];
        $map['club_code'] = $userInfo['clubCode'];
        $map['name'] = $userInfo['name'];
        $map['email'] = $userInfo['email'];
        $map['password'] = Hash::make($userInfo['password']);
        $map['create_time'] = $userInfo['createTime'];
        $map['flag'] = 'CU';

        return $this->create($map);
    }

    public function query_all() {
        return $this->all();
    }

    public function verify_email($email) {
        $map['email'] = $email;

        return $this->where($map)->first();
    }

    public function check_permission($token, $flag) {
        $map['flag'] = $flag;
        $map['token'] = $token;

        return $this->where($map)->first();
    }

    public function query_find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }

    public function update_login_token($uid, $tokenInfo) {
        $map['token'] = $tokenInfo['token'];
        $map['login_time'] = $tokenInfo['loginTime'];

        return $this->where(array('id' => $uid))->update($map);
    }

    public function update_pw_by_email($info) {
        $map['email'] = $info['email'];
        $map1['password'] = Hash::make($info['password']);

        return $this->where($map)->update($map1);
    }

    public function update_club_user_by_code($userInfo) {
        $map['code'] = $userInfo['code'];
        $map1['name'] = $userInfo['name'];
        $map1['email'] = $userInfo['email'];
        $map1['club_code'] = $userInfo['clubCode'];

        return $this->where($map)->update($map1);
    }

    public function delete_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->delete();
    }

    public function find_by_code($userCode) {
        $map['code'] = $userCode;

        return $this->where($map)->first();
    }

}
