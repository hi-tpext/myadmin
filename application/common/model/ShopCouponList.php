<?php

namespace app\common\model;

use app\admin\controller\Shopcouponlist as ControllerShopcouponlist;
use think\Model;
use think\model\concern\SoftDelete;

class ShopCouponList extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected $updateTime = false;

    use SoftDelete;

    protected static function init()
    {
        self::afterDelete(function ($data) {
            ShopCouponType::where(['id' => $data['coupon_type_id']])->setInc('del_num');
        });
    }

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['nickname'] : '--');
    }

    public function getUsernameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['username'] : '--');
    }

    public function getTypeNameAttr($value, $data)
    {
        $type = ShopCouponType::get($data['coupon_type_id']);
        return $type ? $type['name'] : '--';
    }

}
