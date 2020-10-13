<?php

namespace app\common\model;

use think\Model;

class MemberSumary extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    public function getAvatarAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member['has_avatar'];
    }

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['nickname'] : '--';
    }

    public function getLevelNameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['agent_level_name'] : '--';
    }

    public function getAgentLevelNameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['agent_level_name'] : '--';
    }

}
