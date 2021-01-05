<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class ShopOrderDelivery extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'datetime';

    public function order()
    {
        return $this->belongsTo(Shoporder::class, 'order_id', 'id');
    }
}
