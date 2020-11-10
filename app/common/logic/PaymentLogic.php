<?php

namespace app\common\logic;

use app\common\model;

class PaymentLogic
{
    protected $msg = [];

    protected $debug = false;

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getMsg()
    {
        return $this->msg;
    }

    public function debug($val = true)
    {
        $this->debug = $val;
    }

    /**
     * Undocumented function
     *
     * @param string $order_sn 格式 order_23_1593307455/recharge_34_1593307345
     * @param array $extData
     * @return boolean
     */
    public function paySuccess($order_sn, $extData = [])
    {
        if (!empty($order_sn)) {
            return false;
        }

        $arr = explode('_', $order_sn);

        if (count($arr) > 2) {
            $type = $arr[0];
            $id = intval($arr[1]);
            if ($type == 'order') {
                return $this->orderPaySuccess($id, $extData);
            } else if ($type == 'recharge') {
                return $this->rechargePaySuccess($id, $extData);
            }
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param array $extData
     * @return boolean
     */
    public function orderPaySuccess($order_id, $extData)
    {
        if (empty($order_id)) {
            return false;
        }

        $orderModel = new model\ShopOrder();

        $order = $orderModel->where(['id' => $order_id, 'pay_status' => 0])->find();

        if (!$order) {
            return true;
        }

        $data = ['pay_status' => 1, 'pay_time' => date('Y-m-d H:i:s')];

        if (!empty($extData) && isset($extData['transaction_id'])) //data里面有微信交易id，写入订单里面，以支持后续退款操作
        {
            $data['transaction_id'] = $extData['transaction_id'];
        }

        if (!empty($extData) && isset($extData['pay_code'])) {
            $data['pay_code'] = $extData['pay_code'];
        }

        $orderModel->update($data, ['id' => $order_id]); //先保存一次，防止支付接口重复通知

        $orderLogic = new OrderLogic($order['member_id']);

        $orderLogic->logOrder($order_id, '订单付款成功', '付款成功', $order['member_id']);

        $orderData = [];

        if ($order['order_status'] == 0) { //待确认
            $orderData['order_status'] = 1;
        }

        $orderModel->update($orderData, ['id' => $order_id]);

        $cartLogic = new CartLogic($order['member_id']);

        $cartLogic->minusStock($order_id);

        $member = model\Member::find($order['member_id']);

        if (!$member) {
            //
        } else {
            $this->goodsProfit($member, $order); //推荐奖励
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param int $recharge_id
     * @param array $extData
     * @return boolean
     */
    public function rechargePaySuccess($recharge_id, $extData)
    {
        if (empty($recharge_id)) {
            return false;
        }

        $rechargeModel = new model\MemberRecharge();

        $recharge = $rechargeModel->where(['id' => $recharge_id, 'pay_status' => 0])->find();

        if (!$recharge) {
            return true;
        }

        $data = ['pay_status' => 1, 'pay_time' => date('Y-m-d H:i:s')];

        if (!empty($extData) && isset($extData['transaction_id'])) //data里面有微信交易id，写入订单里面，以支持后续退款操作
        {
            $data['transaction_id'] = $extData['transaction_id'];
        }

        if (!empty($extData) && isset($extData['pay_code'])) {
            $data['pay_code'] = $extData['pay_code'];
        }

        $rechargeModel->update($data, ['id' => $recharge_id]); //先保存一次，防止支付接口重复通知

        $account = new AccountLogic;

        if (isset(model\MemberRecharge::$get_goods_methds[$recharge['goods_method']])) {

            $money = model\MemberRecharge::$mnoeys[$recharge['goods_method']];

            if ($money['coupon'] > 0) {
                $couponLogic = new CouponLogic;
                $res_cp = $couponLogic->create($recharge['member_id'], 1, $money['coupon']);
            }

            if ($money['points'] > 0) {
                $res = $account->editMemberPoints($recharge['member_id'], $money['points'], '充值获得', 'recharge');
            }
        } else {
            //
        }

        $recharge['goods_price'] = $recharge['account'];

        $member = model\Member::find($recharge['member_id']);
        if (!$member) {
            //
        } else {
            $this->shareProfit($member, $recharge); //分享奖励
            $this->agentLevelUp($member, $recharge); //代理升级
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param array $recharge
     * @return boolean
     */
    public function agentLevelUp($member, $recharge)
    {
        if ($member['agent_level'] < 1) {
            if (!$this->debug) {
                $memberLogic = new MemberLogic;
                $res_lv = $memberLogic->changeAgentLevel($recharge['member_id'], 1, '充值成功，代理等级提升');
                if ($res_lv['code'] != 1) {
                    return false;
                } else {
                    return true;
                }
            } else {

            }
        } else {

        }

        return true;
    }

    public function shareProfit($member, $recharge)
    {

    }

    public function goodsProfit($member, $order)
    {

    }
}
