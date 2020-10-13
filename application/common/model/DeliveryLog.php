<?php

namespace app\common\model;

use think\Model;
use tpext\areacity\api\model\Areacity;

class DeliveryLog extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::max('sort') + 5;
            }
        });
    }

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $data['member_id'] . '#' . $member['nickname'] : $data['member_id'] . '#' . '不存在';
    }

    public function getPcatAttr($value, $data)
    {
        $text = '---';

        $province = Areacity::where(['id' => $data['province']])->find();

        if ($province) {

            $text = $province['ext_name'];
            $city = Areacity::where(['id' => $data['city']])->find();

            if ($city) {

                $text .= ',' . $city['ext_name'];
                $area = Areacity::where(['id' => $data['area']])->find();

                if ($area) {

                    $text .= ',' . $area['ext_name'];

                    $town = Areacity::where(['id' => $data['town']])->find();

                    if ($town) {

                        $text .= ',' . $town['ext_name'];
                    }
                }
            }
        }

        return $text;
    }
}
