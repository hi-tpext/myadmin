<?php

namespace app\common\model;

use think\Model;

class ShopOrderAction extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    protected $updateTime = false;

    public function order()
    {
        return $this->belongsTo(Shoporder::class, 'order_id', 'id');
    }
}
