<?php

namespace app\common\model;

use think\Model;

class MemberSale extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    /**
     * 兑出中
     */
    public const STATUS_0 = 0;

    /**
     * 确认兑入会员
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

    public static $status_types = [
        self::STATUS_0 => '兑出中',
        self::STATUS_1 => '已确认兑入会员，等待付款',
        self::STATUS_2 => '兑入会员已付款',
        self::STATUS_3 => '兑出会员确认收款',
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

    public function getStatusTextAttr($value, $data)
    {
        return isset(static::$status_types[$data['status']]) ? static::$status_types[$data['status']] : '--';
    }

    public function getSoldTotalAttr($value, $data)
    {
        return MemberSaleAsk::where(['sale_id' => $data['id'], 'status' => 3])->sum('sale_num');
    }
}
