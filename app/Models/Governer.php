<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Governer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'token',
        'password',
        'login_time',
        'create_time',
        'flag'
    ];

    public function update_login_token($uid, $tokenInfo) {
        $map['token'] = $tokenInfo['token'];
        $map['login_time'] = $tokenInfo['loginTime'];

        return $this->where(array('id' => $uid))->update($map);
    }

    public function verify_email($userName) {
        $map['email'] = $userName;

        return $this->where($map)->first();
    }

    public function update_pw_by_email($info) {
        $map['email'] = $info['email'];
        $map1['password'] = Hash::make($info['password']);

        return $this->where($map)->update($map1);
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
}
