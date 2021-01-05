<?php

namespace app\common\logic;

use app\common\model;
use think\facade\Db;
use think\facade\Log;
use think\validate;

class OrderLogic
{
    protected $member_id = 0;
    protected $session_id = 0;

    public function __construct($member_id, $session_id = '')
    {
        $this->member_id = $member_id;
        $this->session_id = $session_id;
    }

    /**
     * Undocumented function
     *
     * @param array $orderInfo
     * @return array
     */
    public function validateOrder($orderInfo)
    {
        if (empty($this->member_id)) {
            return ['code' => 0, 'msg' => 'member_id不能为空'];
        }
        $member = (new model\Member())->find($this->member_id);
        if (!$member) {
            return ['code' => 0, 'msg' => '会员不存在'];
        }

        if ($member['status'] == 0) {
            return ['code' => 0, 'msg' => '会员已禁用'];
        }

        $orderInfo['member_points'] = $member['points'];
        $orderInfo['member_coupon_ok'] = model\ShopCouponList::where(['member_id' => $this->member_id])->where(['coupon_type_id' => 1, 'status' => 1, 'order_id' => 0])->count();
        $orderInfo['member_money'] = $member['money'];
        $orderInfo['member_commission'] = $member['commission'];

        $v = validate::make([
            'goods_list|产品' => 'require|array',
        ]);

        if (true !== $v->check($orderInfo)) {
            return ['code' => 0, 'msg' => '参数有误-' . $v->getError()];
        }

        $couldBuy = null;
        $member_price = 0;
        $shipping_price = 0;
        $weight = 0;

        $cart_goods_list = $orderInfo['goods_list'];

        $goods_list = [];

        $spec_keys = [];

        $cartLogic = new CartLogic($this->member_id, $this->session_id);

        $goods_ids = [];
        $goods_total_num = 0;

        foreach ($cart_goods_list as $g) {
            $v = validate::make([
                'goods_id|产品id' => 'require|number|gt:0',
                'goods_num|产品数量' => 'require|number|gt:0',
            ]);

            if (true !== $v->check($g)) {
                return ['code' => 0, 'msg' => '参数有误-' . $v->getError()];
            }

            if (!isset($g['spec_key'])) {
                $g['spec_key'] = '';
            }

            $couldBuy = $cartLogic->couldBuy($g['goods_id'], $g['goods_num'], $g['spec_key']);

            if ($couldBuy['code'] != 1) {
                return $couldBuy;
            }

            $goods = $couldBuy['goods']; //对应产品

            $specPrice = $couldBuy['spec_price'];

            $goods['goods_sku'] = '';
            $goods['spec_key'] = $g['spec_key'];
            $goods['spec_key_name'] = '';
            $goods['goods_num'] = $g['goods_num'];

            if ($specPrice) { //有规格
                $goods['sale_price'] = $specPrice['sale_price'];
                $goods['spec_key_name'] = $specPrice['spec_key_name'];
                $goods['goods_sku'] = $specPrice['sku'];
            }

            $goods['is_del'] = isset($g['is_del']) ? $g['is_del'] : 0;
            $goods['is_add'] = isset($g['is_add']) ? $g['is_add'] : 0;
            $goods['order_goods_id'] = isset($g['order_goods_id']) ? $g['order_goods_id'] : 0;

            $goods_key = $g['goods_id'] . $g['spec_key'];

            if (!$goods['is_del'] && in_array($goods_key, $spec_keys)) {
                return ['code' => 0, 'msg' => '产品规格重复-' . $goods['name'] . '#' . ($goods['spec_key_name'] ? $goods['goods_sku'] . $goods['spec_key_name'] : $goods['spu'])];
            }

            $spec_keys[] = $goods_key;

            $goods['member_price'] = $cartLogic->memberPrice($goods, $specPrice);

            if (!$goods['is_del']) {
                $member_price += $goods['member_price'] * $g['goods_num'];
                $weight += $goods['weight'] ?: 1;
            }
            $goods_list[] = $goods;

            $goods_ids[$g['goods_id']] = $g['goods_id'];

            $goods_total_num += $g['goods_num'];
        }

        $orderInfo['goods_list'] = $goods_list;
        $orderInfo['goods_ids'] = $goods_ids;
        $orderInfo['goods_total_num'] = $goods_total_num;

        $v = validate::make([
            'address_id|收货地址' => 'require|number',
            'shipping_code|物流code' => 'require',
            'use_money|使用余额' => 'require|float|>=:0',
            'use_points|使用积分' => 'require|float|>=:0',
            'use_coupon_num|使用提货券' => 'require|number|>=:0',
        ]);

        if (true !== $v->check($orderInfo)) {
            return ['code' => 0, 'msg' => '参数有误-' . $v->getError(), 'order_info' => $orderInfo];
        }

        $orderInfo['goods_price'] = $member_price; //产品合计

        $address = (new model\MemberAddress())->find($orderInfo['address_id']);

        if (!$address) {
            $orderInfo['shipping_price'] = 0;
            $orderInfo['order_amount'] = $member_price;
            $orderInfo['total_amount'] = $member_price;
            return ['code' => 0, 'msg' => '收货地址不存在', 'order_info' => $orderInfo];
        }

        $address['select_address'] = $address['pcat'];

        $orderInfo['address'] = $address;

        $shippingLogic = new ShippingLogic;

        $shipping = $shippingLogic->shippignInfo($address, $orderInfo['shipping_code'], $weight);

        if ($shipping['code'] != 1) {
            $orderInfo['shipping_price'] = 0;
            $orderInfo['order_amount'] = $member_price;
            $orderInfo['total_amount'] = $member_price;
            $shipping['order_info'] = $orderInfo;
            return $shipping;
        }

        $shipping_price = $shipping['money'];
        $orderInfo['shipping_com'] = $shipping['shipping_com'];
        $orderInfo['shipping_name'] = $shipping['shipping_com']['name'];

        $orderInfo['shipping_price'] = $shipping['money']; //运费

        $orderInfo['total_amount'] = $member_price + $shipping_price;

        $order_amount = $orderInfo['total_amount']; //实付金额
        $orderInfo['order_amount'] = $order_amount; //合计金额

        $accoutLogic = new AccountLogic;

        $coupon_price = 0;

        if ($orderInfo['use_coupon_num'] > 0) {

            $orderInfo['use_coupon_num'] = $goods_total_num;

            if ($orderInfo['use_coupon_num'] > $orderInfo['member_coupon_ok']) {
                return ['code' => 0, 'msg' => '可用提货券数量不足，本次需使用' . $goods_total_num . '张，您只有' . $member['coupon_ok'] . '张', 'order_info' => $orderInfo];
            }

            $coupon = model\ShopCouponType::find(1);
            if (!$coupon) {
                return ['code' => 0, 'msg' => '系统错误-提货券类型未找到', 'order_info' => $orderInfo];
            }
            $for_goods = array_filter(explode(',', $coupon['for_goods']));

            foreach ($goods_list as $g) {
                if (!in_array($g['id'], $for_goods)) {
                    return ['code' => 0, 'msg' => '[' . $g['name'] . ']不能使用提货券', 'order_info' => $orderInfo];
                }

                $coupon_price += $g['member_price'] * $g['goods_num'];
            }
        }

        $orderInfo['coupon_price'] = $coupon_price;

        $order_amount -= $coupon_price; //提货券抵扣

        if ($orderInfo['use_points'] > 0) {
            $orderInfo['use_points'] = $order_amount;
            $enouth = $accoutLogic->hasEnouth($this->member_id, 'points', $orderInfo['use_points']);
            if ($enouth['code'] != 1) {
                $orderInfo['use_points'] = $enouth['current'];
            }
        }

        if ($orderInfo['use_money'] > 0) {
            $orderInfo['use_money'] = $order_amount;
            $enouth = $accoutLogic->hasEnouth($this->member_id, 'money', $orderInfo['use_money']);
            if ($enouth['code'] != 1) {

                $orderInfo['use_money'] = $enouth['current'];
            }
        }

        if ($orderInfo['use_commission'] > 0) {
            $orderInfo['use_commission'] = $order_amount;
            $enouth = $accoutLogic->hasEnouth($this->member_id, 'commission', $orderInfo['use_commission']);
            if ($enouth['code'] != 1) {
                $orderInfo['use_commission'] = $enouth['current'];
            }
        }

        $points_rate = $this->pointsRate();

        $orderInfo['points_money'] = $orderInfo['use_points'] / $points_rate; //积分抵扣

        if ($orderInfo['points_money'] > $order_amount) {
            $orderInfo['points_money'] = $order_amount;
            $orderInfo['use_points'] = $orderInfo['points_money'] * $points_rate;
        }

        $order_amount -= $orderInfo['points_money']; //花豆抵扣

        if ($orderInfo['use_money'] > $order_amount) {
            $orderInfo['use_money'] = $order_amount;
        }

        $order_amount -= $orderInfo['use_money']; //余额抵扣

        if ($orderInfo['use_commission'] > $order_amount) {
            $orderInfo['use_commission'] = $order_amount;
        }

        $order_amount -= $orderInfo['use_commission']; //密豆抵扣

        if ($order_amount < 0) {
            return ['code' => 0, 'msg' => '系统错误-结算金额有误', 'order_info' => $orderInfo];
        }

        $admin_id = session('admin_id') ?: 0;

        if ($admin_id && isset($orderInfo['discount'])) {
            if ($orderInfo['discount'] < 0) {
                if ($orderInfo['discount'] * -1 > $order_amount) {
                    return ['code' => 0, 'msg' => '价格调整,金额有误。订单应付金额为' . $order_amount . '，最大调整不能超过此金额'];
                }
            }

            $order_amount += $orderInfo['discount']; //价格调整
        }

        $orderInfo['order_amount'] = $order_amount;

        return ['code' => 1, 'msg' => 'ok', 'order_info' => $orderInfo];
    }

    public function create($orderInfo)
    {
        if ((new model\ShopOrder())->where([['member_id', '=', $this->member_id], ['create_time', '>=', date('Y-m-d 00:00:00')]])->count() > 50) {
            return ['code' => 0, 'msg' => '一天只能下50个订单'];
        }

        $isEdit = isset($orderInfo['id']) && $orderInfo['id'] > 0;

        $validate = $this->validateOrder($orderInfo);

        if ($validate['code'] != 1) {
            return $validate;
        }

        $orderInfo = $validate['order_info'];

        $address = $orderInfo['address'];

        $orderInfo = array_merge(
            [
                'invoice_title' => '',
                'coupon_card_type' => '0',
                'coupon_price' => 0,
                'order_prom_id' => 0,
                'order_prom_amount' => 0,
                'discount' => 0,
                'user_note' => '',
                'admin_note' => 0,
            ],
            $orderInfo
        );

        $order_sn = $isEdit ? $orderInfo['order_sn'] : 'sp' . date('YmdHis') . mt_rand(100, 999);

        $data = [
            'order_sn' => $order_sn,
            'member_id' => $this->member_id,
            'address_id' => $orderInfo['address_id'],
            'consignee' => $address['consignee'],
            'province' => $address['province'],
            'city' => $address['city'],
            'area' => $address['area'],
            'town' => $address['town'],
            'address' => $address['address'],
            'mobile' => $address['mobile'],
            'shipping_code' => $orderInfo['shipping_code'],
            'shipping_name' => $orderInfo['shipping_name'],
            'invoice_title' => $orderInfo['invoice_title'],
            'goods_price' => $orderInfo['goods_price'],
            'shipping_price' => $orderInfo['shipping_price'],
            'use_money' => $orderInfo['use_money'],
            'use_points' => $orderInfo['use_points'],
            'use_commission' => $orderInfo['use_commission'],
            'points_money' => $orderInfo['points_money'],
            'coupon_card_type' => $orderInfo['coupon_card_type'],
            'coupon_price' => $orderInfo['coupon_price'],
            'total_amount' => $orderInfo['total_amount'],
            'order_amount' => $orderInfo['order_amount'],
            'order_prom_id' => $orderInfo['order_prom_id'],
            'order_prom_amount' => $orderInfo['order_prom_amount'],
            'discount' => $orderInfo['discount'],
            'user_note' => $orderInfo['user_note'],
            'admin_note' => $orderInfo['admin_note'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];

        Db::startTrans();

        $order_id = 0;

        $ordreModel = new model\ShopOrder();

        if ($isEdit) {
            $ordreModel->update($data, ['id' => $orderInfo['id']]);
            $order_id = $orderInfo['id'];
        } else {
            $res = $ordreModel->save($data);
            if ($res) {
                $order_id = $ordreModel->id;
            }

            $this->logOrder($order_id, '您提交了订单，请等待系统确认', '提交订单', $this->member_id);
        }

        if (!$order_id) {
            return ['code' => 0, 'msg' => '创建订单失败'];
        }

        $goods_list = $orderInfo['goods_list'];

        foreach ($goods_list as $goods) {

            $gdata = [
                'order_id' => $order_id,
                'goods_id' => $goods['id'],
                'goods_name' => $goods['name'],
                'goods_sn' => $goods['spu'],
                'goods_num' => $goods['goods_num'],
                'sale_price' => $goods['sale_price'],
                'member_price' => $goods['member_price'],
                'give_integral' => 0,
                'spec_key' => $goods['spec_key'],
                'spec_key_name' => $goods['spec_key_name'],
                'goods_sku' => $goods['goods_sku'],
                'weight' => $goods['weight'],
                'logo' => $goods['logo'],
            ];

            $orderGoodsModel = new model\ShopOrderGoods();

            if ($isEdit) {
                if ($goods['is_del']) {
                    $res = $orderGoodsModel->destroy($goods['order_goods_id']);
                    if (!$res) {
                        Db::rollback();
                        return ['code' => 0, 'msg' => '保存出错'];
                    }
                } else {
                    if ($goods['is_add']) {
                        $res = $orderGoodsModel->save($gdata);
                        if (!$res) {
                            Db::rollback();
                            return ['code' => 0, 'msg' => '保存出错'];
                        }
                    } else {
                        $res = $orderGoodsModel->save($gdata, ['id' => $goods['order_goods_id']]);
                        if (!$res) {
                            Db::rollback();
                            return ['code' => 0, 'msg' => '保存出错'];
                        }
                    }
                }
            } else {
                $res = $orderGoodsModel->save($gdata);
                if (!$res) {
                    Db::rollback();
                    return ['code' => 0, 'msg' => '保存出错'];
                }
            }
        }

        Db::commit();

        if ($isEdit) { //编辑

            $money = $accountLog = model\MemberAccount::where(['order_id' => $order_id, 'tag' => 'pay'])->sum('money') ?: 0;
            $points = $accountLog = model\MemberAccount::where(['order_id' => $order_id, 'tag' => 'pay'])->sum('points') ?: 0;
            $commission = $accountLog = model\MemberAccount::where(['order_id' => $order_id, 'tag' => 'pay'])->sum('commission') ?: 0;

            if ($money != $data['use_money'] * -1 || $points != $data['use_points'] * -1) {
                $accountLogic = new AccountLogic();
                $accountLog = $accountLogic->editMemberAccount($this->member_id, [
                    'money' => ($money + $data['use_money']) * -1,
                    'points' => ($points + $data['use_points']) * -1,
                    'commission' => ($commission + $data['use_commission']) * -1,
                    'remark' => '购物订单[' . $data['order_sn'] . ']差额调整',
                    'tag' => 'pay',
                    'order_id' => $order_id,
                ]);

                if ($accountLog['code'] != 1) {
                    return ['code' => 0, 'msg' => '差额调整失败-' . $accountLog['msg']];
                }
            }
        } else { //添加模式
            if ($data['use_money'] > 0 || $data['use_points'] > 0) {
                $accountLogic = new AccountLogic();
                $accountLog = $accountLogic->editMemberAccount($this->member_id, [
                    'money' => $data['use_money'] * -1,
                    'points' => $data['use_points'] * -1,
                    'commission' => $data['use_commission'] * -1,
                    'remark' => '购物订单[' . $data['order_sn'] . ']使用',
                    'tag' => 'pay',
                    'order_id' => $order_id,
                ]);

                if ($accountLog['code'] != 1) {
                    return ['code' => 0, 'msg' => '账户扣除失败-' . $accountLog['msg']];
                }
            }

            if ($orderInfo['use_coupon_num'] > 0) {

                $couponList = model\ShopCouponList::where(['member_id' => $this->member_id])
                    ->where(['coupon_type_id' => 1, 'status' => 1, 'order_id' => 0])
                    ->order('get_time')->limit(0, $orderInfo['use_coupon_num'])->column('id');

                if (count($couponList) < $orderInfo['use_coupon_num']) {
                    return ['code' => 0, 'msg' => '提货券扣除失败-可用数量不足'];
                }

                $count = model\ShopCouponList::where(['member_id' => $this->member_id])
                    ->where(['coupon_type_id' => 1, 'status' => 1, 'order_id' => 0])
                    ->where('id', 'in', $couponList)
                    ->update(['order_id' => $order_id, 'use_time' => date('Y-m-d H:i:s')]);

                if ($count) {
                    model\MemberLog::create([
                        'member_id' => $this->member_id,
                        'desc' => '购物使用提货券' . $count . '张',
                        'change' => '',
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);

                    $this->logOrder($order_id, '使用提货券' . $count . '张', '提货券', $this->member_id);
                }
            }
        }

        if ($data['order_amount'] < 0.01) {
            $payLogic = new PaymentLogic;
            $payLogic->orderPaySuccess($order_id, ['pay_code' => 'other']);
        }

        return ['code' => 1, 'msg' => 'ok', 'order_id' => $order_id, 'need_pay' => $data['order_amount'] > 0.01];
    }

    /**
     * Undocumented function
     *
     * @return float
     */
    public function pointsRate()
    {
        return 1; //一块钱等于多少积分
    }

    /**
     * Undocumented function
     *
     * @param integer $order_id
     * @param array $order
     * @return array
     */
    public function orderBtn($order_id = 0, $order = array())
    {
        if (empty($order)) {
            $order = model\ShopOrder::find($order_id);
        }

        /**
         *  订单用户端显示按钮
         * 去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
         * 取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
         * 确认收货  AND shipping_status=1 AND order_status=0
         * 评价      AND order_status=1
         * 查看物流  if(!empty(物流单号))
         */
        $btn_arr = array(
            'pay_btn' => 0, // 去支付按钮
            'cancel_btn' => 0, // 取消按钮
            'receive_btn' => 0, // 确认收货
            'comment_btn' => 0, // 评价按钮
            'shipping_btn' => 0, // 查看物流
            'return_btn' => 0, // 退货按钮 (联系客服)
        );

        // 货到付款
        if ($order['pay_code'] == 'cod') {
            if (($order['order_status'] == 0 || $order['order_status'] == 1) && $order['shipping_status'] == 0) // 待发货
            {
                $btn_arr['cancel_btn'] = 1; // 取消按钮 (联系客服)
            }
            if ($order['shipping_status'] == 1 && $order['order_status'] == 1) //待收货
            {
                $btn_arr['receive_btn'] = 1; // 确认收货
                $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
            }
        } // 非货到付款
        else {
            if ($order['pay_status'] == 0 && $order['order_status'] == 0) // 待支付
            {
                $btn_arr['pay_btn'] = 1; // 去支付按钮
                $btn_arr['cancel_btn'] = 1; // 取消按钮
            }
            if ($order['pay_status'] == 1 && in_array($order['order_status'], array(0, 1)) && $order['shipping_status'] == 0) // 待发货
            {
                $btn_arr['return_btn'] = 1; // 退款退货按钮 (联系客服)
            }
            if ($order['pay_status'] == 1 && $order['order_status'] == 1 && $order['shipping_status'] == 1) //待收货
            {
                $btn_arr['receive_btn'] = 1; // 确认收货
                $btn_arr['return_btn'] = 1; // 退货按钮 (售后客服)
            }
        }
        if ($order['order_status'] == 2) {
            $btn_arr['comment_btn'] = 1; // 评价按钮
            $btn_arr['return_btn'] = 1; // 退货按钮 (售后客服)
        }
        if ($order['shipping_status'] != 0) {
            $btn_arr['shipping_btn'] = 1; // 查看物流
        }
        if ($order['shipping_status'] == 2 && $order['order_status'] == 1) // 部分发货
        {
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }

        return $btn_arr;
    }

    /**
     * Undocumented function
     *
     * @param array $order
     * @return array
     */
    public function set_btn_order_status($order)
    {
        $order_status_arr = model\ShopOrder::$orders_status_desc;
        $order['order_status_code'] = $order_status_code = $this->orderStatusDesc(0, $order); // 订单状态显示给用户看的
        $order['order_status_desc'] = $order_status_arr[$order_status_code];
        $orderBtnArr = $this->orderBtn(0, $order);
        return array_merge($order, $orderBtnArr); // 订单该显示的按钮
    }

    /**
     * 获取订单状态的 中文描述名称
     * @param type $order_id 订单id
     * @param type $order 订单数组
     * @return string
     */
    public function orderStatusDesc($order_id = 0, $order = array())
    {
        if (empty($order)) {
            $order = model\ShopOrder::find($order_id);
        }

        // 货到付款
        if ($order['pay_code'] == 'cod') {
            if (in_array($order['order_status'], array(0, 1)) && $order['shipping_status'] == 0) {
                return 'WAITSEND';
            }
            //'待发货',
        } else // 非货到付款
        {
            //'待支付',
            if ($order['pay_status'] == 0 && $order['order_status'] == 0) {
                return 'WAITPAY';
            }
            //'待发货',
            if ($order['pay_status'] == 1 && in_array($order['order_status'], array(0, 1)) && $order['shipping_status'] != 1) {
                return 'WAITSEND';
            }
        }

        //'待收货',
        if (($order['shipping_status'] == 1) && ($order['order_status'] == 1)) {
            return 'WAITRECEIVE';
        }
        //'待评价',
        if ($order['order_status'] == 2) {
            return 'WAITCCOMMENT';
        }
        //'已取消',
        if ($order['order_status'] == 3) {
            return 'CANCEL';
        }
        //'已完成',
        if ($order['order_status'] == 4) {
            return 'FINISH';
        }
        //'已作废',
        if ($order['order_status'] == 5) {
            return 'CANCELLED';
        }

        return 'OTHER';
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param integer $member_id
     * @return array
     */
    public function confirmOrder($order_id, $member_id = 0)
    {
        $where = [['id', '=', $order_id]];

        if ($member_id) {
            $where[] = [
                'member_id', '=', $member_id,
            ];
        }

        $orderModel = new model\ShopOrder();

        $order = $orderModel->where($where)->find();

        if ($order['order_status'] != model\ShopOrder::ORDER_STATUS_1) {
            return ['code' => 0, 'msg' => '该订单不能收货确认'];
        }

        $data['order_status'] = 2;
        $data['confirm_time'] = date('Y-m-d H:i:s');

        if ($order['pay_code'] == 'cod') {
            $data['pay_status'] = 1;
            $data['pay_time'] = date('Y-m-d H:i:s');
        }

        $res = $orderModel->save($data, ['id' => $order_id]);

        if (!$res) {
            return ['code' => 0, 'msg' => '操作失败'];
        }

        $this->logOrder($order_id, $member_id ? '确认收货' : '系统自动确认', '确认收货', session('admin_id'));

        if ($order['order_prom_id'] > 0) {
            return ['code' => 1, 'msg' => '操作成功'];
        }

        $accountLogic = new AccountLogic();

        $rebateLogModel = new model\RebateLog();
        $memberModel = new model\Member();

        $rebateLogs = $rebateLogModel
            ->where(['order_id' => $order_id, 'order_sn' => $order['order_sn'], 'enable' => 1])
            ->where('status', '<', 3)->select(); ////0未付款,1已付款, 2等待分成(未收货) 3已分成, 4已取消

        if (count($rebateLogs) > 0) {

            $member = $memberModel->find($order['member_id']);

            foreach ($rebateLogs as $rlog) {
                $data = [
                    'points' => $rlog['points'],
                    'commission' => $rlog['commission'],
                    'remark' => "会员[{$member['nickname']}]订单完分成",
                ];

                $accountLog = $accountLogic->editMemberAccount($order['member_id'], $data);

                if ($accountLog['code'] == 1) {
                    $date = date('Y-m-d H:i:s');
                    $rebateLogModel->update(['status' => 3, 'confirm_time' => $date, 'confirm' => $date], ['id' => $rlog['id']]);
                }
            }
        }

        return ['code' => 1, 'msg' => '操作成功'];
    }

    /**
     * Undocumented function
     *
     * @param int $order_id
     * @param int $member_id
     * @param int $remark
     * @return array
     */
    public function cancelOrder($order_id, $member_id, $remark = '')
    {
        Log::alert('取消订单：' . $order_id);

        $where = [['id', '=', $order_id]];

        if ($member_id) {
            $where[] = [
                'member_id', '=', $member_id,
            ];
        }

        $order = model\ShopOrder::find($order_id);

        if (!$order) {
            return ['code' => 0, 'msg' => '订单不存在'];
        }

        //检查是否未支付的订单
        if ($order['pay_status'] > 0 || $order['order_status'] > 1) {
            return ['code' => 0, 'msg' => '支付状态或订单状态不允许'];
        }

        //有余额支付或积分的情况
        if ($order['use_money'] > 0 || $order['use_points'] > 0 || $order['use_commission'] > 0) {
            $data = [
                'money' => $order['use_money'],
                'points' => $order['use_points'],
                'remark' => '订单' . $order['order_sn'] . '订单取消退还已使用余额',
            ];
            Log::alert('使用余额：' . $order['use_money'] . '，使用积分：' . $order['use_points']);

            $accountLoginc = new AccountLogic;
            $res_a = $accountLoginc->editMemberAccount($order['member_id'], $data);
            if ($res_a['code'] != 1) {
                Log::alert('取消失败，用户账户操作失败-' . $res_a['msg']);
                return ['code' => 0, 'msg' => '取消失败，用户余额操作失败-' . $res_a['msg']];
            }

            $this->logOrder($order_id, '退还余额：' . $order['use_money'] . '，退还花豆：' . $order['use_points'] . '，退还蜜豆：' . $order['use_commission'], '余额返还', session('admin_id'));

            $orderModel_0 = new model\ShopOrder;
            $res_oa = $orderModel_0->save(['use_money' => 0, 'use_points' => 0, 'points_money' => 0], ['id' => $order_id]);
            if (!$res_oa) {
                Log::alert('更新订单余额、花豆、蜜豆信息失败-' . $res_a['msg']);
            }
        }

        $orderModel = new model\ShopOrder;

        $res_o = $orderModel->save(['order_status' => 3, 'coupon_price' => 0], ['id' => $order_id]);

        if (!$res_o) {
            Log::alert('更新订单状态失败');
        }

        $res_c = model\ShopCouponList::where(['order_id' => $order_id])->update(['order_id' => 0, 'use_time' => null]); //恢复已使用的优惠券

        if (!$res_c) {
            Log::alert('无优惠券或退还失败');
        }

        $rebateLogModel = new model\RebateLog();

        $res_rb = $rebateLogModel->where(['order_id' => $order_id, 'order_sn' => $order['order_sn']])
            ->update(['status' => 4, 'remark' => '订单取消', 'enable' => 0]);

        if (!$res_rb) {
            Log::alert('无分成记录或取消失败');
        }

        $this->logOrder($order_id, $member_id ? '用户取消订单' : '系统取消订单', '订单已取消', session('admin_id'));

        Log::alert('取消成功');

        return ['code' => 1, 'msg' => '操作成功'];
    }

    /**
     * 订单操作日志
     * 参数示例
     * @param type $order_id 订单id
     * @param type $action_note 操作备注
     * @param type $status_desc 操作状态  提交订单, 付款成功, 取消, 等待收货, 完成
     * @return boolean
     */
    public function logOrder($order_id, $action_note, $status_desc)
    {
        $orderActionModel = new model\ShopOrderAction();
        $order = model\ShopOrder::find($order_id);
        if (!$order) {
            return true;
        }

        $action_info = array(
            'order_id' => $order_id,
            'order_sn' => $order['order_sn'],
            'admin_id' => session('admin_id') ?: 0,
            'order_status' => $order['order_status'],
            'shipping_status' => $order['shipping_status'],
            'pay_status' => $order['pay_status'],
            'action_note' => $action_note,
            'status_desc' => $status_desc,
            'log_time' => time(),
        );

        return $orderActionModel->save($action_info);
    }
}
