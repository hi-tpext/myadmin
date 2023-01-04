<?php

namespace app\common\logic;

use app\common\model;

class CartLogic
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
     * @param int $goods_id
     * @param integer $num
     * @param string $spec_key
     * @param string $tag
     * @return array
     */
    public function addCart($goods_id, $num = 0, $spec_key = '', $tag = '')
    {
        if ($num < 1) {
            return ['code' => 0, 'msg' => '购买商品数量不能为0'];
        }

        $cartCount = 0;

        if (empty($this->member_id) && empty($this->session_id)) {
            return ['code' => 0, 'msg' => 'member_id,session_id不能同时为空！'];
        }

        if ($this->member_id && $this->session_id) {
            $cartCount = model\ShopCart::where(['session_id' => $this->session_id])
                ->whereOr(['member_id' => $this->member_id])->count();
        } else if ($this->member_id) {

            $cartCount = model\ShopCart::where(['member_id' => $this->member_id])->count();
        } else {

            $cartCount = model\ShopCart::where(['session_id' => $this->session_id])->count();
        }

        if ($cartCount > 20) {
            return ['code' => 0, 'msg' => '购物车最多只能放20种商品'];
        }

        $couldBuy = $this->couldBuy($goods_id, $num, $spec_key);

        if ($couldBuy['code'] != 1) {
            return $couldBuy;
        }

        $goods = $couldBuy['goods'];
        $specPrice = $couldBuy['spec_price'];

        $spec_key_name = '';
        $goods_sku = '';

        if ($specPrice) { //有规格
            $spec_key_name = $specPrice['spec_key_name'];
            $goods_sku = $specPrice['sku'];
        }

        $exits = null;

        if ($this->member_id && $this->session_id) {
            $exits = model\ShopCart::where(['session_id' => $this->session_id, 'goods_id' => $goods_id, 'spec_key' => $spec_key])
                ->whereOr(['member_id' => $this->member_id, 'goods_id' => $goods_id, 'spec_key' => $spec_key])->find();
        } else if ($this->member_id) {
            $exits = model\ShopCart::where(['member_id' => $this->member_id, 'goods_id' => $goods_id, 'spec_key' => $spec_key])->find();
        } else {
            $exits = model\ShopCart::where(['session_id' => $this->session_id, 'goods_id' => $goods_id, 'spec_key' => $spec_key])->find();
        }

        $data = [
            'goods_id' => $goods_id,
            'member_id' => $this->member_id,
            'session_id' => $this->session_id,
            'goods_num' => $num,
            'goods_spu' => $goods['spu'],
            'goods_sku' => $goods_sku,
            'spec_key' => $spec_key,
            'spec_key_name' => $spec_key_name,
            'goods_name' => $goods['name'],
            'market_price' => $goods['market_price'],
            'sale_price' => $specPrice ? $specPrice['sale_price'] : $goods['sale_price'],
            'member_price' => $this->memberPrice($goods, $specPrice),
            'weight' => $goods['weight'],
            'tag' => $tag,
        ];

        $cartModel = new model\ShopCart();

        $id = 0;

        if ($exits) {
            $res = $exits->save($data);
            $id = $exits['id'];
        } else {
            $res = $cartModel->exists(false)->save($data);
            $id = $cartModel['id'];
        }

        if ($res) {
            return ['code' => 1, 'msg' => '加入购物车成功', 'id' => $id, 'num' => $data['goods_num']];
        }

        return ['code' => 0, 'msg' => '操作失败', 'id' => 0];
    }

    public function remove($id)
    {
        $res = 0;
        if ($this->member_id) {
            $res = model\ShopCart::where(['member_id' => $this->member_id, 'id' => $id])->delete();
        } else {
            $res = model\ShopCart::where(['session_id' => $this->session_id, 'id' => $id])->delete();
        }

        if ($res) {
            return ['code' => 1, 'msg' => '删除成功'];
        }

        return ['code' => 0, 'msg' => '删除失败'];
    }

    public function cartList()
    {
        if (empty($this->member_id) && empty($this->session_id)) {
            return ['code' => 0, 'msg' => 'member_id,session_id不能同时为空！'];
        }

        $cartList = [];
        if ($this->member_id && $this->session_id) {
            $cartList = model\ShopCart::where(['session_id' => $this->session_id])
                ->whereOr(['member_id' => $this->member_id])->select();
        } else if ($this->member_id) {
            $cartList = model\ShopCart::where(['member_id' => $this->member_id])->select();
        } else {
            $cartList = model\ShopCart::where(['session_id' => $this->session_id])->select();
        }

        $member_price = 0;
        $goods_price = 0;

        $list = [];

        foreach ($cartList as $cartGoods) {
            $couldBuy = $this->couldBuy($cartGoods['goods_id'], $cartGoods['goods_num'], $cartGoods['spec_key']);

            $cartGoods['can_buy'] = 1;
            $cartGoods['error'] = '';

            if ($couldBuy['code'] != 1) {
                $cartGoods['can_buy'] = 0;
                $cartGoods['error'] = $couldBuy['msg'];
                if ($couldBuy['spec_price'] && $cartGoods['goods_num'] > $couldBuy['spec_price']['stock']) {
                    $cartGoods['goods_num'] = $couldBuy['spec_price']['stock'];
                    model\ShopCart::where(['id' => $cartGoods['id']])->update(['goods_num' => $couldBuy['spec_price']['stock']]);
                }
                if (isset($couldBuy['goods']) && $cartGoods['goods_num'] > $couldBuy['goods']['stock']) {
                    $cartGoods['goods_num'] = $couldBuy['goods']['stock'];
                    model\ShopCart::where(['id' => $cartGoods['id']])->update(['goods_num' => $couldBuy['goods']['stock']]);
                }
            }

            $goods = $couldBuy['goods']; //对应产品

            if (empty($goods)) { //产品已删除
                $cartGoods['spec_rice'] = null;
                model\ShopCart::where(['id' => $cartGoods['id']])->delete();
                continue;
            }

            $specPrice = isset($couldBuy['spec_price']) ? $couldBuy['spec_price'] : null;
            $cartGoods['member_price'] = $this->memberPrice($goods, $specPrice);
            $cartGoods['sale_price'] = $specPrice ? $specPrice['sale_price'] : $goods['sale_price'];
            $cartGoods['market_price'] = $goods['market_price'];
            $cartGoods['goods_name'] = $goods['name'];
            $cartGoods['spec_key_name'] = $specPrice ? $specPrice['spec_key_name'] : '';
            $cartGoods['spec_rice'] = $specPrice;
            $cartGoods['goods_logo'] = $goods['logo'];

            $member_price += $cartGoods['member_price'] * $cartGoods['goods_num'];
            $goods_price += $cartGoods['sale_price'] * $cartGoods['goods_num'];

            $list[] = $cartGoods;
        }

        return ['code' => 1, 'msg' => 'ok', 'list' => $list, 'data' => ['goods_price' => $goods_price, 'member_price' => $member_price]];
    }

    /**
     * 计算订单信息
     *
     * @param string|array $selected
     * @param array $data
     * @return array
     */
    public function summary($selected, $data = [])
    {
        if (empty($this->member_id)) {
            return ['code' => 0, 'msg' => 'member_id不能为空！'];
        }

        if (empty($selected)) {
            return ['code' => 0, 'msg' => 'selected不能为空！'];
        }

        $cartModel = new model\ShopCart();

        $cartList = $cartModel->where(['member_id' => $this->member_id])->where('id', 'in', $selected)->select();

        if (empty($cartList)) {
            return ['code' => 0, 'msg' => '未选中任何产品！'];
        }

        $goods_list = [];
        foreach ($cartList as $cartGoods) {
            $goods_list[] = [
                'goods_id' => $cartGoods['goods_id'],
                'goods_num' => $cartGoods['goods_num'],
                'spec_key' => $cartGoods['spec_key'],
            ];
        }

        $data['goods_list'] = $goods_list;

        $orderLogic = new OrderLogic($this->member_id);

        $validate = $orderLogic->validateOrder($data);

        return $validate;
    }

    /**
     * 提交订单
     *
     * @param string|array $selected
     * @param array $data
     * @return array
     */
    public function submit($selected, $data = [])
    {
        if (empty($this->member_id)) {
            return ['code' => 0, 'msg' => 'member_id不能为空！'];
        }

        if (empty($selected)) {
            return ['code' => 0, 'msg' => 'selected不能为空！'];
        }

        $cartModel = new model\ShopCart();

        $cartList = $cartModel->where(['member_id' => $this->member_id])->where('id', 'in', $selected)->select();

        if (empty($cartList)) {
            return ['code' => 0, 'msg' => '未选中任何产品！'];
        }

        $goods_list = [];
        foreach ($cartList as $cartGoods) {
            $goods_list[] = [
                'goods_id' => $cartGoods['goods_id'],
                'goods_num' => $cartGoods['goods_num'],
                'spec_key' => $cartGoods['spec_key'],
            ];
        }

        $data['goods_list'] = $goods_list;

        $orderLogic = new OrderLogic($this->member_id);

        $res = $orderLogic->create($data);

        if ($res['code'] == 1) {
            $cartModel->where(['member_id' => $this->member_id])->where('id', 'in', $selected)->delete(); //清空购物车
        }

        return $res;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function pushSessionData()
    {
        if (empty($this->member_id) || empty($this->session_id)) {
            return ['code' => 0, 'msg' => 'member_id或session_id不能为空！'];
        }

        $cartModel = new model\ShopCart();

        $cartModel->update(['member_id' => $this->member_id], ['session_id' => $this->session_id]);
    }

    /**
     * Undocumented function
     *
     * @param array $goods
     * @return float
     */
    public function memberPrice($goods, $specPrice)
    {
        //TODO 是否有折扣之类的

        if ($specPrice) {
            $specPrice['sale_price'];
        }

        return $goods['sale_price'];
    }

    /**
     * Undocumented function
     *
     * @param int $goods_id
     * @return array
     */
    public function couldBuyGoods($goods_id)
    {
        $goodsModel = new model\ShopGoods();

        $goods = $goodsModel->field('id,name,spu,sale_price,market_price,cost_price,stock,on_sale,is_show,weight,logo')
            ->find($goods_id);

        if (!$goods) {
            return ['code' => 0, 'msg' => '产品不存在', 'goods' => null, 'spec_price' => null];
        }
        if (!$goods['on_sale']) {
            return ['code' => 0, 'msg' => '产品已下架', 'goods' => $goods, 'spec_price' => null];
        }
        if ($goods['stock'] < 1) {
            return ['code' => 0, 'msg' => '产品库存不足', 'goods' => $goods, 'spec_price' => null];
        }

        return ['code' => 1, 'msg' => 'ok', 'goods' => $goods, 'spec_price' => null];
    }

    /**
     * Undocumented function
     *
     * @param integer $goods_id
     * @param integer $num
     * @param string $spec_key
     * @return array
     */
    public function couldBuyGoodsSpec($goods_id, $num = 0, $spec_key = '')
    {
        $specPriceModel = new model\ShopGoodsSpecPrice();

        $manyPrice = $specPriceModel->where(['goods_id' => $goods_id])->count();

        if ($manyPrice > 0) {
            if (empty($spec_key)) {
                return ['code' => 0, 'msg' => '参数有误，必须传递产品规格', 'spec_price' => null];
            }

            $specPrice = $specPriceModel->where(['goods_id' => $goods_id, 'spec_key' => $spec_key])
                ->field('id,goods_id,spec_key,spec_key_name,sku,sale_price,stock')->find();

            if (!$specPrice) {
                return ['code' => 0, 'msg' => '产品规格不存在-' . $spec_key, 'spec_price' => null];
            }

            if ($specPrice['stock'] < $num) {
                return ['code' => 0, 'msg' => '规格[' . $specPrice['spec_key_name'] . ']库存不够,你只能买' . $specPrice['stock'] . '件', 'spec_price' => $specPrice];
            }

            return ['code' => 1, 'msg' => 'ok', 'spec_price' => $specPrice];
        } else {
            if (!empty($spec_key)) {
                return ['code' => 0, 'msg' => '参数有误，产品不存在多个规格', 'spec_price' => null];
            }

            return ['code' => 1, 'msg' => 'ok', 'spec_price' => null];
        }
    }

    /**
     * Undocumented function
     *
     * @param int $goods_id
     * @param integer $num
     * @param string $spec_key
     * @return array
     */
    public function couldBuy($goods_id, $num = 0, $spec_key = '')
    {
        $couldBuyGoods = $this->couldBuyGoods($goods_id);

        if ($couldBuyGoods['code'] != 1) {
            return $couldBuyGoods;
        }

        $goods = $couldBuyGoods['goods'];

        $couldBuyGoodsSpec = $this->couldBuyGoodsSpec($goods_id, $num, $spec_key);

        if ($couldBuyGoodsSpec['code'] != 1) {
            $couldBuyGoodsSpec['goods'] = $goods;
            return $couldBuyGoodsSpec;
        }

        $specPrice = $couldBuyGoodsSpec['spec_price'];

        if (!$specPrice) {
            if ($goods['stock'] < $num) {
                return ['code' => 0, 'msg' => '产品[' . $goods['name'] . ']库存不够,你只能买' . $goods['stock'] . '件', 'goods' => $goods, 'spec_price' => $specPrice];
            }
        }

        return ['code' => 1, 'msg' => 'ok', 'goods' => $goods, 'spec_price' => $specPrice];
    }

    /**
     * 刷新商品库存, 如果商品有设置规格库存, 则商品总库存 等于 所有规格库存相加
     *
     * @param int $goods_id
     * @return mixed
     */
    public function refreshStock($goods_id)
    {
        $specPriceModel = new model\ShopGoodsSpecPrice();
        $goodsModel = new model\ShopGoods();

        $count = $specPriceModel->where(['goods_id' => $goods_id])->count();

        if ($count == 0) {
            return false;
        }
        $allStock = $specPriceModel->where(['goods_id' => $goods_id])->sum('stock');

        $res = $goodsModel->save(['stock' => $allStock], ['id' => $goods_id]);

        return $res;
    }

    /**
     * order_goods 表扣除商品库存
     *
     * @param int $order_id
     * @return mixed
     */
    public function minusStock($order_id)
    {
        $goodsModel = new model\ShopGoods();
        $specPriceModel = new model\ShopGoodsSpecPrice();
        $orderGoodsModel = new model\ShopOrderGoods();

        $orderGoodsArr = $orderGoodsModel->where(['order_id' => $order_id])->select();

        foreach ($orderGoodsArr as $val) {

            if (!empty($val['spec_key'])) {
                $specPriceModel->where(['goods_id' => $val['goods_id'], 'spec_key' => $val['spec_key']])->setDec('stock', $val['goods_num']); // 减规格库存
                $this->refreshStock($val['goods_id']);
            } else {
                $goodsModel->where(['id' => $val['goods_id']])->setDec('stock', $val['goods_num']); // 减库存
            }

            $goodsModel->where(['id' => $val['goods_id']])->setInc('sales_sum', $val['goods_num']); // 加销量
        }
    }

    /**
     * 恢复库存
     *
     * @param int $order_id
     * @return mixed
     */
    public function plusStock($order_id)
    {
        $goodsModel = new model\ShopGoods();
        $specPriceModel = new model\ShopGoodsSpecPrice();
        $orderGoodsModel = new model\ShopOrderGoods();

        $orderGoodsArr = $orderGoodsModel->where(['order_id' => $order_id])->select();
        foreach ($orderGoodsArr as $val) {

            if (!empty($val['spec_key'])) {
                $specPriceModel->where(['goods_id' => $val['goods_id'], 'spec_key' => $val['spec_key']])->setInc('stock', $val['goods_num']); // 加规格库存
                $this->refreshStock($val['goods_id']);
            } else {
                $goodsModel->where(['id' => $val['goods_id']])->setInc('stock', $val['goods_num']); // 加库存
            }

            $goodsModel->where(['id' => $val['goods_id']])->setDec('sales_sum', $val['goods_num']); // 减销量
        }
    }
}
