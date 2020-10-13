<?php

namespace app\common\model;

use think\Model;

class MemberSaleAsk extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    /**
     * 兑入中
     */
    public const STATUS_0 = 0;

    /**
     * 已确认兑入会员，等待付款
     */
    public const STATUS_1 = 1;

    /**
     * 兑入会员已付款
     */
    public const STATUS_2 = 2;

    /**
     * 兑出会员确认收款
     */
    public const STATUS_3 = 3;

    /**
     * 兑入会员取消
     */
    public const STATUS_4 = 4;

    /**
     * 未中标
     */
    public const STATUS_5 = 5;

    /**
     * 超时关闭
     */
    public const STATUS_6 = 6;

    public static $status_types = [
        self::STATUS_0 => '兑入中',
        self::STATUS_1 => '待付款',
        self::STATUS_2 => '兑入会员已付款',
        self::STATUS_3 => '兑出会员确认收款',
        self::STATUS_4 => '兑入会员取消',
        self::STATUS_5 => '未中标',
        self::STATUS_6 => '超时关闭',
    ];

    public function getAvatarAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member['has_avatar'];
    }

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['nickname'] : '--';
    }

    public function getMobileAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['mobile'] : '--';
    }

    public function getSaleAvatarAttr($value, $data)
    {
        $member = Member::get($data['sale_member_id']);
        return $member['has_avatar'];
    }

    public function getSaleNicknameAttr($value, $data)
    {
        $member = Member::get($data['sale_member_id']);
        return $member ? $member['nickname'] : '--';
    }

    public function getSaleMobileAttr($value, $data)
    {
        $member = Member::get($data['sale_member_id']);
        return $member ? $member['mobile'] : '--';
    }

    public function getStatusTextAttr($value, $data)
    {
        return isset(static::$status_types[$data['status']]) ? static::$status_types[$data['status']] : '--';
    }
}
