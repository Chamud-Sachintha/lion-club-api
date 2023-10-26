<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProofDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'create_time'
    ];

    public function add_log($info) {
        $map['code'] = $info['documentCode'];
        $map['name'] = $info['documentName'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_by_code($code) {
        $map['code'] = $code;

        return $this->where($map)->first();
    }

    public function update_docuemnt_details_by_code($documentInfo) {
        $map['code'] = $documentInfo['documentCode'];
        $info['name'] = $documentInfo['documentName'];
        
        return $this->where($map)->update($info);
    }

    public function query_all() {
        return $this->all();
    }
}
