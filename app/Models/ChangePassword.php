<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'secret',
        'flag',
        'time'
    ];

    public function add_log($info) {
        $map['email'] = $info['userEmail'];
        $map['password'] = Hash::make($info['password']);
        $map['secret'] = $info['secret'];
        $map['flag'] = $info['flag'];
        $map['time'] = $info['createTime'];

        return $this->create($map);
    }

    public function query_find($info) {
        $map['email'] = $info['email'];

        return $this->where($map)->first();
    }

    public function find_by_secret($secret) {
        $map['secret'] = $secret;

        return $this->where($map)->first();
    }

    public function delete_log_by_email($email) {
        $map['email'] = $email;

        return $this->where($map)->delete();
    }
}
