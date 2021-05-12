<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class ShopCouponList extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    protected $updateTime = false;

    use SoftDelete;

    public static function onAfterDelete($data)
    {
        ShopCouponType::where(['id' => $data['coupon_type_id']])->setInc('del_num');
    }

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

    public function getTypeNameAttr($value, $data)
    {
        $type = ShopCouponType::find($data['coupon_type_id']);
        return $type ? $type['name'] : '--';
    }
}
