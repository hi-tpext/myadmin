<?php

namespace app\common\model;

use think\Model;
use tpext\shop\common\model\ShopGoodsSpecPrice;

class ShopGoodsSpec extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeWrite($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::where(['goods_id' => $data['goods_id']])->max('sort') + 1;
        }
    }
}
