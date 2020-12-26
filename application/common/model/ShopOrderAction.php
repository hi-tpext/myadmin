<?php

namespace app\common\model;

use app\admin\controller\Shoporder;
use think\Model;

class ShopOrderAction extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected $updateTime = false;

    protected static function init()
    {

    }

    public function order()
    {
        return $this->belongsTo(Shoporder::class, 'order_id', 'id');
    }
}
