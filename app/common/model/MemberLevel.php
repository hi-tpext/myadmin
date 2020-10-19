<?php

namespace app\common\model;

use think\Model;

class MemberLevel extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public function getMemberCountAttr($value, $data)
    {
        return Member::where('level', $data['level'])->count();
    }
}
