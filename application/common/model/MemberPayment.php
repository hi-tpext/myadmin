<?php

namespace app\common\model;

use think\Model;

class MemberPayment extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    /**
     * 银行卡
     */
    public const PAYMENT_TYPE_BANK = 1;

    /**
     * 支付宝
     */
    public const PAYMENT_TYPE_ALIPAY = 2;

    /**
     * 微信
     */
    public const PAYMENT_TYPE_WX = 3;

    public static $payment_types = [
        self::PAYMENT_TYPE_BANK => '银行卡',
        self::PAYMENT_TYPE_ALIPAY => '支付宝',
        self::PAYMENT_TYPE_WX => '微信',
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

    public function getAccountInfoAttr($value, $data)
    {
        if ($data['payment_type'] == self::PAYMENT_TYPE_BANK) {
            return '卡号:' . $data['pay_card_id'] . ',开户行:' . $data['pay_account'] . ',姓名:' . $data['member_name'];
        } else if ($data['payment_type'] == self::PAYMENT_TYPE_ALIPAY) {
            return '支付宝账户:' . $data['alipay_account'] . ',姓名:' . $data['member_name'];
        } else if ($data['payment_type'] == self::PAYMENT_TYPE_WX) {
            return '微信账户:' . $data['openid'] . ($data['is_h5'] ? ',h5' : ',app') . ',姓名:' . $data['member_name'];
        }

        return '未知支付类型';
    }
}
