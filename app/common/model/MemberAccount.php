<?php

namespace app\common\model;

use think\Model;

class MemberAccount extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    protected $updateTime = false;

    public static $types = [
        'points' => '积分',
        'money' => '余额',
        'commission' => '佣金',
    ];

    public function getNicknameAttr($value, $data)
    {
        $member = Member::find($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['nickname'] : '--');
    }

    public function getUsernameAttr($value, $data)
    {
        $member = Member::find($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['username'] : '--');
    }

    public static function getNames()
    {
        $names = implode('/', array_values(self::$types));

        return $names;
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
