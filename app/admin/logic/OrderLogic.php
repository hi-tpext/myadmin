<?php

namespace app\admin\logic;

use app\common\logic\AccountLogic;
use app\common\logic\CartLogic;
use app\common\logic\OrderLogic as CommonOrderLogic;
use app\common\logic\PaymentLogic;
use app\common\model;
use think\Db;
use think\Validate;
use tpext\areacity\api\model\Areacity;

class OrderLogic
{
    /**
     * Undocumented function
     *
     * @param int $member_id
     * @return array
     */
    public function getAddressList($member_id)
    {
        $addressList = Db('member_address')->alias('a')
            ->join('member m', 'a.member_id = m.id')
            ->where('a.member_id', 'eq', $member_id)
            ->order('a.is_default desc')->field('a.*,m.nickname,m.mobile as m_mobile')->select();

        $data = [];
        $areacityModel = new Areacity();
        foreach ($addressList as $li) {

            $text = '';

            $province = $areacityModel->where(['id' => $li['province']])->find();

            if ($province) {

                $text .= $province['ext_name'];
                $city = $areacityModel->where(['id' => $li['city']])->find();

                if ($city) {

                    $text .= ',' . $city['ext_name'];
                    $area = $areacityModel->where(['id' => $li['area']])->find();

                    if ($area) {

                        $text .= ',' . $area['ext_name'];

                        $town = $areacityModel->where(['id' => $li['town']])->find();
                        if ($town) {

                            $text .= ',' . $town['ext_name'];
                        }
                    }
                }
            }

            $data[] = [
                'id' => $li['id'],
                'text' => '收货人:' . $li['consignee'] . ',手机:' . $li['mobile'] . ',地址:' . $text . ',' . $li['address'],
            ];
        }

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param array $order
     * @return array
     */
    public function getAdminButton($order)
    {
        /*
         *  操作按钮汇总 ：付款、设为未付款、确认、取消确认、无效、去发货、确认收货、申请退货
         *
         */
        $os = $order['order_status']; //订单状态
        $ss = $order['shipping_status']; //发货状态
        $ps = $order['pay_status']; //支付状态
        $btn = [];
        if ($order['pay_code'] == 'cod') {
            if ($os == 0 && $ss == 0) {
                $btn['confirm'] = '确认';
            } else if ($os == 1 && $ss != 1) {
                $btn['delivery'] = '去发货';
                $btn['un_confirm'] = '取消确认';
            } else if ($ss == 1 && $os == 1 && $ps == 0) {
                $btn['pay'] = '付款';
            } else if ($ps == 1 && $ss == 1 && $os == 1) {
                $btn['pay_cancel'] = '设为未付款';
            } else if ($ps == 0 && $ss == 0 && ($os == 1 || $os == 0)) {
                $btn['ordre_cancel'] = '取消订单';
            }
        } else {
            if ($ps == 0 && $os == 0) {
                $btn['pay'] = '付款';
                $btn['ordre_cancel'] = '取消订单';
            } else if ($os == 0 && $ps == 1) {
                $btn['pay_cancel'] = '设为未付款';
                $btn['confirm'] = '确认';
            } else if ($os == 1 && $ps == 1 && $ss != 1) {
                $btn['un_confirm'] = '取消确认';
                $btn['delivery'] = '去发货';
            } else if ($ps == 0 && $ss == 0 && ($os == 1 || $os == 0)) {
                $btn['ordre_cancel'] = '取消订单';
            }
        }

        if ($ss == 1 && $os == 1 && $ps == 1) {
            $btn['delivery_confirm'] = '确认收货';
            //$btn['refund'] = '申请退货';
        } else if ($os == 2 || $os == 4) {
            //$btn['refund'] = '申请退货';
        } else if ($os == 3 || $os == 5) {
            $btn['remove'] = '移除';
        }
        if ($os != 5) {
            $btn['invalid'] = '无效';
        }
        return $btn;
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param string $act
     * @return boolean|int
     */
    public function orderProcessHandle($order_id, $act)
    {
        $updata = [];
        switch ($act) {
            case 'pay': //付款
                $payLogic = new PaymentLogic;
                $payLogic->orderPaySuccess($order_id, ['pay_code' => 'other']);
                return true;
            case 'pay_cancel': //取消付款
                //
                return true;
            case 'confirm': //确认订单
                $updata['order_status'] = 1;
                break;
            case 'ordre_cancel': //取消订单
                $comOrderLogic = new CommonOrderLogic(0);
                $comOrderLogic->cancelOrder($order_id, 0); // 调用取消
                return true;
            case 'un_confirm': //取消确认
                $updata['order_status'] = 0;
                break;
            case 'invalid': //作废订单
                $updata['order_status'] = 5;
                break;
            case 'remove': //移除订单
                return $this->delOrder($order_id);
            case 'delivery_confirm': //确认收货
                $comOrderLogic = new CommonOrderLogic(0);
                $comOrderLogic->confirmOrder($order_id); // 调用确认收货按钮
                return true;
            default:
                return true;
        }
        $orderModel = new model\ShopOrder();
        $res = $orderModel->isUpdate(true)->save($updata, ['id' => $order_id]); //改变订单状态
        return $res;
    }

    /**
     * 删除订单
     *
     * @param int $order_id
     * @return boolean
     */
    public function delOrder($order_id)
    {
        $orderModel = new model\ShopOrder();

        $res = $orderModel->destroy($order_id);
        return $res;
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param int $refund_type 0 => '退款到用户余额', 1 => '原路退回第三方支付账户[开发中...]', 2 => '已通过其他方式退款'
     * @param float $refund_amount
     * @return array
     */
    public function orderPayCancel($order_id, $refund_type, $refund_amount)
    {
        $orderModel = new model\ShopOrder();
        $rebateLogModel = new model\RebateLog();

        $order = $orderModel->get($order_id);

        $options = [0 => '退款到用户余额', 1 => '原路退回第三方支付账户[开发中...]', 2 => '已通过其他方式退款'];

        if (!$order) {
            return ['code' => 0, 'msg' => '订单不存在'];
        }

        if ($order['pay_status'] != 1) {
            return ['code' => 0, 'msg' => '订单不是【已支付】状态'];
        }

        if (($refund_type == 0 || $refund_type == 1) && $refund_amount > $order['order_amount']) {
            return ['code' => 0, 'msg' => '退款金额不能超过订单支付金额：' . $order['order_amount']];
        }

        Db::startTrans();

        model\ShopOrder::where(['id' => $order_id])->update(['pay_status' => 0, 'order_status' => 0, 'pay_time' => null]);

        $cartLogic = new CartLogic(0);

        $cartLogic->plusStock($order_id); //恢复库存

        $accountLogic = new AccountLogic;

        $orderLogic = new CommonOrderLogic(0);

        $rebateLogModel->isUpdate(true, ['order_id' => $order_id, 'order_sn' => $order['order_sn']])->save(['status' => 0]);

        if ($refund_type == 0 && $refund_amount > 0) {
            $accountLogic = new AccountLogic;

            $refundRes = $accountLogic->editMemberMoney($order['member_id'], $refund_amount, '退款到用户余额');

            if ($refundRes['code'] != 1) {
                Db::rollback();
                return $refundRes;
            }

            $accountLogic->updateMemberLevel($order['member_id']);
        }

        if ($refund_type == 1 && $refund_amount > 0) {
            return ['code' => 0, 'msg' => '开发中'];
        }

        Db::commit();

        $orderLogic->logOrder($order_id, '订单取消付款', $options[$refund_type] ?? '取消付款');

        return ['code' => 1, 'msg' => '取消支付成功'];
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param array $data
     * @param array|string $goods_ids
     * @return array
     */
    public function delivery($order_id, $data, $goods_ids)
    {
        $orderModel = new model\ShopOrder();
        $orderGoodsModel = new model\ShopOrderGoods();

        $order = $orderModel->get($order_id);

        $v = Validate::make([
            'order_id|订单编号' => 'require|number',
            'shipping_code|物流公司' => 'require',
            'invoice_no|物流单号' => 'require',
        ]);

        if (true !== $v->check($data)) {
            return ['code' => 0, 'msg' => '参数有误-' . $v->getError()];
        }

        $admin = \tpext\myadmin\admin\model\AdminUser::current();

        $data['order_sn'] = $order['order_sn'];
        $data['member_id'] = $order['member_id'];
        $data['admin_id'] = $admin['id'];
        $data['admin_group_id'] = $admin['group_id'];
        $data['consignee'] = $order['consignee'];
        $data['mobile'] = $order['mobile'];
        $data['province'] = $order['province'];
        $data['city'] = $order['city'];
        $data['area'] = $order['area'];
        $data['town'] = $order['town'];
        $data['address'] = $order['address'];
        $data['shipping_name'] = $order['shipping_name'];
        $data['shipping_price'] = $order['shipping_price'];
        $data['create_time'] = date('Y-m-d H:i:s');

        if ($data['shipping_code'] != $order['shipping_code']) {
            $shippingcom = new model\ShippingCom;
            $shipping_com = $shippingcom->where(['code' => $data['shipping_code']])->find();

            if (empty($shipping_com)) {
                return ['code' => 0, 'msg' => '物流公司不存在-' . $data['shipping_code']];
            }

            $data['shipping_name'] = $shipping_com['name'];
        }

        $id = Db::name('delivery_log')->insertGetId($data);

        if (!$id) {
            return ['code' => 0, 'msg' => '生成物流记录失败'];
        }

        $where = [
            ['order_id', 'eq', $order_id],
            ['id', 'in', $goods_ids],
            ['is_send', 'eq', 0],
        ];

        $res = $orderGoodsModel->isUpdate(true, $where)->save(['is_send' => 1, 'delivery_id' => $id]);

        if (!$res) {
            Db::rollback();
            return ['code' => 0, 'msg' => '更新产品状态失败'];
        }

        $sendCount = $orderGoodsModel->where(['order_id' => $order_id, 'is_send' => 1])->count();
        $allCount = $orderGoodsModel->where(['order_id' => $order_id])->count();

        $res = $orderModel->isUpdate(true, ['id' => $order_id])
            ->save(['shipping_status' => $sendCount < $allCount ? 2 : 1, 'shipping_time' => date('Y-m-d H:i:s')]);

        if (!$res) {
            Db::rollback();
            return ['code' => 0, 'msg' => '更新订单态失败'];
        }

        Db::commit();

        $orderLogic = new CommonOrderLogic(0);

        $orderLogic->logOrder($order_id, '订单' . ($sendCount < $allCount ? '部分发货' : '发货'), '物流公司：' . $data['shipping_name'] . '，物流单号：' . $data['invoice_no'] . $data['note']);

        return ['code' => 1, 'msg' => $sendCount < $allCount ? '部分发货成功' : '订单所有产品发货完成'];
    }
}
