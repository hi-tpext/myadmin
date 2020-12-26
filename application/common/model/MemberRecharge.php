<?php

namespace app\common\model;

use think\Model;

class MemberRecharge extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected $updateTime = false;

    /**
     * 未支付
     */
    public const PAY_STATUS_0 = 0;
    /**
     * 已支付
     */
    public const PAY_STATUS_1 = 1;
    /**
     * 交易关闭
     */
    public const PAY_STATUS_2 = 2;

    public const PAY_CODE_WX_PAY = 'wxpay';
    public const PAY_CODE_ALI_PAY = 'alipay';
    public const PAY_CODE_OTHER = 'other';

    /**
     * 套餐一：消费99元 1份产品，赠100积分
     */
    public const GET_GOODS_METHOD_1 = 1;

    /**
     * 套餐二：消费999元 10份产品，赠1100积分
     */
    public const GET_GOODS_METHOD_2 = 2;

    public static $pay_status_types = [
        self::PAY_STATUS_0 => '未支付',
        self::PAY_STATUS_1 => '已支付',
        self::PAY_STATUS_2 => '交易关闭',
    ];

    public static $pay_codes = [
        self::PAY_CODE_WX_PAY => '微信支付',
        self::PAY_CODE_ALI_PAY => '支付宝',
        self::PAY_CODE_OTHER => '其他',
    ];

    public static $get_goods_methds = [
        self::GET_GOODS_METHOD_1 => '套餐一：消费99元 1份产品，赠100积分',
        self::GET_GOODS_METHOD_2 => '套餐二：消费999元 10份产品，赠1100积分',
    ];

    public static $mnoeys = [
        self::GET_GOODS_METHOD_1 => ['money' => 99, 'coupon' => 1, 'points' => 100, 'buy_num' => 1],
        self::GET_GOODS_METHOD_2 => ['money' => 999, 'coupon' => 10, 'points' => 1100, 'buy_num' => 10],
    ];

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['nickname'] : '--');
    }

    public function getUsernameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $data['member_id'] . '#' . ($member ? $member['username'] : '--');
    }

    public function getFirstLeaderNameAttr($value, $data)
    {
        $member = Member::get($data['first_leader']);
        return $data['first_leader'] . '#' . ($member ? $member['nickname'] : '--');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'id', 'member_id');
    }
}
