<?php

namespace app\common\model;

use think\Model;

class MemberSignTrans extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected $updateTime = false;

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

    public function getRechargeSnAttr($value, $data)
    {
        if ($data['game_queue_id'] > 0) {
            return '蜂狂GO,游戏编号:' . $data['game_queue_id'];
        }

        $recharge = MemberRecharge::get($data['recharge_id']);

        return $recharge ? $recharge['order_sn'] : '--';
    }

    public function getMoneyAttr($value, $data)
    {
        if ($data['game_queue_id'] > 0) {

            $gameQueue = FlowerQueue::get($data['game_queue_id']);

            if ($gameQueue) {
                if ($gameQueue['prize_type'] == FlowerQueue::PRIZE_3) {
                    return '319';
                } else if ($gameQueue['prize_type'] == FlowerQueue::PRIZE_7) {
                    return '958';
                }
            }

            return '--';
        }

        $recharge = MemberRecharge::get($data['recharge_id']);

        return $recharge ? ($recharge['account'] + $recharge['use_commission'] + $recharge['use_re_comm']) : '--';
    }

    public function getBuyNumAttr($value, $data)
    {
        $recharge = MemberRecharge::get($data['recharge_id']);

        return $recharge ? $recharge['buy_num'] : '--';
    }

    public function getPayTimeAttr($value, $data)
    {
        if ($data['recharge_id'] > 0) {
            $recharge = MemberRecharge::get($data['recharge_id']);

            return $recharge ? $recharge['pay_time'] : '--';
        } else {
            $gameQueue = FlowerQueue::get($data['game_queue_id']);

            return $gameQueue ? $gameQueue['create_time'] : '--';
        }
    }
}
