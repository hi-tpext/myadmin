<?php

namespace app\common\model;

use think\Model;
use tpext\shop\common\model\ShopGoodsSpecPrice;

class ShopGoodsSpec extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::where(['goods_id' => $data['goods_id']])->max('sort') + 1;
            }
        });
    }
}
