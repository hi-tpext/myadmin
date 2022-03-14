<?php

namespace app\common\model;

use think\Model;

class ShopGoodsSpecPrice extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeWrite($data)
    {
        if (empty($data['sku'])) {
            $data['sku'] = 'sk' . str_pad($data['goods_id'] . date('YmdHis'), 18, "0", STR_PAD_LEFT);
        }
    }
}
