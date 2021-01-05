<?php

namespace app\common\model;

use think\Model;

class ShopOrderGoods extends Model
{
    protected $updateTime = false;

    protected $createTime = false;

    public function order()
    {
        return $this->belongsTo(Shoporder::class, 'order_id', 'id');
    }
}
