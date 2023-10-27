<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivtyPointReserve extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_code',
        'club_code',
        'points',
        'time'
    ];

    public function add_log($info) {
        $map['club_activity_code'] = $info['clubActivityCode'];
        $map['club_code'] = $info['clubCode'];
        $map['points'] = $info['points'];
        $map['time'] = $info['createTime'];

        return $this->create($map);
    }
}
