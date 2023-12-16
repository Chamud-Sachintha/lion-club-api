<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForgotPasswordLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'role',
        'code',
        'create_time'
    ];

    public function add_log($logInfo) {
        $map['email'] = $logInfo['email'];
        $map['role'] = $logInfo['role'];
        $map['code'] = $logInfo['code'];
        $map['create_time'] = $logInfo['createTime'];

        return $this->create($map);
    }

    public function find_by_email($email) {
        $map['email'] = $email;

        return $this->where($map)->first();
    }

    public function find_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }

    public function delete_by_email($email) {
        $map['email'] = $email;

        return $this->where($map)->delete();
    }
}
