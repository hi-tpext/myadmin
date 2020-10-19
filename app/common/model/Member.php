<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use tpext\areacity\api\model\Areacity;

class Member extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'datetime';

    public function getLevelNameAttr($value, $data)
    {
        $level = MemberLevel::where('level', $data['level'])->find();
        if ($level) {
            return $level['name'];
        }

        if ($data['level'] == 0) {
            return '普通会员';
        }

        return '--';
    }

    public function getHasAvatarAttr($value, $data)
    {
        return $data && $data['avatar'] ? $data['avatar'] : '/static/images/touxiang.png';
    }

    public function getAgentLevelNameAttr($value, $data)
    {
        $level = AgentLevel::where('level', $data['agent_level'])->find();
        if ($level) {
            return $level['name'];
        }

        if ($data['level'] == 0) {
            return '非代理';
        }

        return '--';
    }

    public function getCouponOkAttr($value, $data)
    {
        return ShopCouponList::where(['member_id' => $data['id']])->where(['coupon_type_id' => 1, 'status' => 1, 'order_id' => 0])->count();
    }

    public function getPcaAttr($value, $data)
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
                }
            }
        }

        return $text;
    }

    public function getIdNameAttr($value, $data)
    {
        return $data['id'] . '#' . $data['nickname'];
    }

    public function getLeaderAttr($value, $data)
    {
        $leader = static::where('id', $data['first_leader'])->find();
        return $leader ? $leader['id_name'] : '--';
    }
}
