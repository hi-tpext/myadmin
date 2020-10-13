<?php

namespace app\common\model;

use think\Model;

class ShippingArea extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::afterDelete(function ($data) {
            ShippingAreaItem::where(['area_id' => $data['id']])->delete();
        });
    }

    public function getPcatAttr($value, $data)
    {
        $itemList = ShippingAreaItem::where(['area_id' => $data['id']])->select();

        if (!count($itemList)) {
            return '';
        }

        $texts = [];
        $n = 0;
        foreach ($itemList as $item) {
            $n += 1;
            $texts[] = $item['pcat'];
            if ($n > 6) {
                $n = 0;
                $texts[] = '<br/>';
            }
        }

        return implode(' <span style="color:red;">*</span> ', $texts);
    }

    public function getComNamesAttr($value, $data)
    {
        if (empty($data['com_codes'])) {
            return '--';
        }

        $coms = ShippingCom::where('code', 'in', trim($data['com_codes'], ','))->where('enable', 1)->column('name');

        return implode(',', $coms);
    }
}
