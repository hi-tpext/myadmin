<?php

namespace app\common\logic;

use app\common\model;
use think\facade\Db;

class CouponLogic
{
    public function create($member_id, $coupon_type_id, $num)
    {
        if (!$member_id || $coupon_type_id <= 0 || $num <= 0) {
            return ['code' => 0, 'msg' => '参数有误'];
        }

        $couponType = model\ShopCouponType::find($coupon_type_id);

        if (!$couponType) {
            return ['code' => 0, 'msg' => '优惠券类型不存在'];
        }

        $list = [];
        for ($i = 1; $i <= $num; $i += 1) {
            $data = [
                'member_id' => $member_id,
                'coupon_type_id' => $coupon_type_id,
                'no_' => '0', //非线下，无卡号
                'code' => $member_id . '_' . $i . $this->get_rand_str(8, 0, 1), //赠送的，就不需要严格不重复了，随便生成一个
                'create_time' => date('Y-m-d H:i:s'),
                'order_id' => 0,
                'get_time' => date('Y-m-d H:i:s'),
                'status' => 1,
                'card_type' => $couponType['card_type'],
            ];
            $list[] = $data;
        }

        $coupon = new model\ShopCouponList;

        $res = $coupon->saveAll($list);

        $c = count($res);

        if ($c > 0) {
            model\MemberLog::create([
                'member_id' => $member_id,
                'desc' => "获得优惠券:{$couponType['name']}{$c}张.",
                'change' => '',
                'create_time' => date('Y-m-d  H:i:s'),
            ]);

            model\ShopCouponType::where(['id' => $coupon_type_id])->setInc('send_num', $c);
            model\ShopCouponType::where(['id' => $coupon_type_id])->setInc('get_num', $c);

            $prefix = config('database.prefix');

            $sql = "UPDATE {$prefix}shop_coupon_list SET `no_` = 10000 + `id` where `no_` = 0";

            Db::execute($sql);

            return ['code' => 1, 'msg' => '发送成功提货券' . $c . '张'];
        }

        return ['code' => 0, 'msg' => '发送提货券失败'];
    }

    public function get_rand_str($randLength = 6, $addtime = 1, $includenumber = 0)
    {
        if ($includenumber) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
        } else {
            $chars = 'abcdefghijklmnopqrstuvwxyz';
        }
        $len = strlen($chars);
        $randStr = '';
        for ($i = 0; $i < $randLength; $i++) {
            $randStr .= $chars[rand(0, $len - 1)];
        }
        $tokenvalue = $randStr;
        if ($addtime) {
            $tokenvalue = $randStr . time();
        }
        return $tokenvalue;
    }
}
