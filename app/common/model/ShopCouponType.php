<?php

namespace app\common\model;

use think\Model;

class ShopCouponType extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public function setForGoodsAttr($value)
    {
        if (empty($value)) {
            return '';
        }
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return ',' . trim($value) . ',';
    }

    public function getUseNumAttr($value, $data)
    {
        $count = ShopCouponList::where(['coupon_type_id' => $data['id']])->where('order_id', '>', 0)->count();
        return $count;
    }
}
