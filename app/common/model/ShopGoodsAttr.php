<?php

namespace app\common\model;

use think\Model;

class ShopGoodsAttr extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeWrite($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::where(['goods_id' => $data['goods_id']])->max('sort') + 1;
        }
    }
}
