<?php

namespace app\common\model;

use think\Model;
use tpext\areacity\api\model\Areacity;

class ShippingAreaItem extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public function getPcatAttr($value, $data)
    {
        $text = '';

        $province = Areacity::where(['id' => $data['province']])->find();

        if ($province) {

            $text .= $province['ext_name'];
            $city = Areacity::where(['id' => $data['city']])->find();

            if ($city) {

                $text .= '-' . $city['ext_name'];
                $area = Areacity::where(['id' => $data['area']])->find();

                if ($area) {

                    $text .= '-' . $area['ext_name'];

                    $town = Areacity::where(['id' => $data['town']])->find();

                    if ($town) {

                        $text .= '-' . $town['ext_name'];
                    }
                }
            }
        }

        if ($data['province'] == 0) {
            $text = '全国默认';
        }

        return $text;
    }

    public function getComNamesAttr($value, $data)
    {
        if (empty($data['com_codes'])) {
            return '--';
        }

        $coms = ShippingCom::where('code', 'in', trim($data['com_codes'], ','))->column('name');

        return implode(',', $coms);
    }
}
