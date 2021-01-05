<?php

namespace app\common\model;

use think\Model;
use tpext\areacity\api\model\Areacity;

class MemberAddress extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public function getNicknameAttr($value, $data)
    {
        $member = Member::find($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['nickname'] : '--');
    }

    public function getPcatAttr($value, $data)
    {
        $text = '';

        $province = Areacity::where(['id' => $data['province']])->find();

        if ($province) {

            $text .= $province['ext_name'];
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

    public function toData()
    {
        $data = [
            'consignee' => 0,
            'province' => 0,
            'city' => 0,
            'area' => 0,
            'town' => 0,
            'address' => $this->address,
            'mobile' => $this->mobile,
        ];

        $province = Areacity::where(['id' => $this->province])->find();
        if ($province) {
            $data['province'] = $province['ext_name'];
            $city = Areacity::where(['id' => $this->province])->find();
            if ($city) {
                $data['city'] = $city['ext_name'];
                $area = Areacity::where(['id' => $this->area])->find();
                if ($area) {
                    $data['area'] = $area['ext_name'];
                    $town = Areacity::where(['id' => $this->town])->find();
                    if ($town) {
                        $data['town'] = $town['ext_name'];
                    }
                }
            }
        }

        return $data;
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
