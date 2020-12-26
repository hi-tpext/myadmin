<?php

namespace app\common\model;

use think\Model;

class ShopOrderGoods extends Model
{
    protected $updateTime = false;

    protected $createTime = false;

    protected static function init()
    {

    }

    public function order()
    {
        return $this->belongsTo(Shoporder::class, 'id', 'order_id');
    }
}
