<?php

namespace app\common\model;

use think\Model;

class AgentLevel extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    public function getMemberCountAttr($value, $data)
    {
        return Member::where('agent_level', $data['level'])->count();
    }
}
