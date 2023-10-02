<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ZonalChairPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
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
        $map['name'] = $userInfo['name'];
        $map['email'] = $userInfo['email'];
        $map['password'] = Hash::make($userInfo['password']);
        $map['create_time'] = $userInfo['createTime'];
        $map['flag'] = 'ZC';

        return $this->create($map);
    }

    public function verify_email($email) {
        $map['email'] = $email;

        return $this->where($map)->first();
    }

    public function update_login_token($uid, $tokenInfo) {
        $map['token'] = $tokenInfo['token'];
        $map['login_time'] = $tokenInfo['loginTime'];

        return $this->where(array('id' => $uid))->update($map);
    }
}
