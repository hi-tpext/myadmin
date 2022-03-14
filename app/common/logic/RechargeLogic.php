<?php

namespace app\common\logic;

use app\common\model;
use think\Validate;
use tpext\common\model\WebConfig;

class RechargeLogic
{
    public function create($member_id, $data)
    {
        $v = new Validate([
            'use_child_account|是否使用子账户' => 'require',
            'goods_method|方式' => 'require|float|egt:0',
        ]);

        if (true !== $v->check($data)) {
            return ['code' => 0, 'msg' => '参数有误-' . $v->getError()];
        }

        $config_key = 'commission';

        $member = model\Member::find($member_id);
        if (!$member) {
            return ['code' => 0, 'msg' => '会员不存在：' . $member_id];
        }

        if ($member['agent_level'] < 1 && $data['use_child_account']) {
            return ['code' => 0, 'msg' => '会员不还不是代理，不能使用子账号'];
        }

        $count = model\MemberRecharge::where(['member_id' => $member_id])->where('create_time', '>=', date('Y-m-d'))->count();

        if ($count > 20) {
            return ['code' => 0, 'msg' => '今天创建了太多充值订单'];
        }

        if (!isset(model\MemberRecharge::$mnoeys[$data['goods_method']])) {
            return ['code' => 0, 'msg' => '未知套餐'];
        }

        $money = model\MemberRecharge::$mnoeys[$data['goods_method']];

        $rechargeModel = new model\MemberRecharge();

        $sdata = [
            'order_sn' => 'rc' . date('YmdHis') . mt_rand(100, 999),
            'member_id' => $member_id,
            'account' => $money['money'],
            'money' => $money['money'],
            'pay_time' => null,
            'pay_code' => '',
            'pay_status' => 0,
            'pkg_id' => 0,
            'remark' => isset($data['remark']) ? $data['remark'] : '',
            'create_time' => date('Y-m-d H:i:s'),
            'transaction_id' => '',
            'goods_method' => $data['goods_method'],
            'buy_num' => $money['buy_num'],
            'first_leader' => $member['first_leader'],
        ];

        $res = $rechargeModel->save($sdata);

        if (!$res) {
            return ['code' => 0, 'msg' => '创建充值订单失败'];
        }

        if ($sdata['account'] == 0) {//待支付金额为0，自动支付成功
            $logic = new PaymentLogic;
            $logic->rechargePaySuccess($rechargeModel['id'], ['pay_code' => 'other']);
        }

        return ['code' => 1, 'msg' => 'ok', 'recharge_id' => $rechargeModel['id']];
    }
}
