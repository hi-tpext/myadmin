<?php

namespace app\common\logic;

use app\common\model;
use think\validate;

class ShippingLogic
{
    /**
     * Undocumented function
     *
     * @param array $address
     * @param string $shipping_code
     * @param array $goods
     * @return float
     */
    public function shippignInfo($address, $shipping_code, $weight)
    {
        $shipping_code = strtoupper($shipping_code);

        $v = validate::make([
            'province|省份' => 'require|number|gt:0',
            'city|城市' => 'require|number|gt:0',
            'area|地区' => 'require|number|gt:0',
            'town|街道' => 'number',
        ]);

        if (true !== $v->check($address)) {
            return ['code' => 0, 'msg' => '参数有误-' . $v->getError()];
        }

        $shippingComModel = new model\ShippingCom();
        $areaItemModel = new model\ShippingAreaItem();

        $shipping_com = $shippingComModel->where(['code' => $shipping_code])->find();

        if (!$shipping_com) {
            return ['code' => 0, 'msg' => '物流公司编码有误'];
        }

        if ($shipping_com['enable'] == 0) {
            return ['code' => 0, 'msg' => '物流公司[' . $shipping_com['name'] . ']未启用'];
        }

        $item = null;

        if ($address['town']) {
            $item = $areaItemModel->alias('item')->join('shipping_area area', 'item.area_id=area.id')
                ->where(['item.town' => $address['town'], 'area.enable' => 1])
                ->whereLike('area.com_codes', '%,' . $shipping_code . ',%')
                ->field('item.*,area.first_weight,money,second_weight,add_money')->find();
        }
        if (!$item) {
            $item = $areaItemModel->alias('item')->join('shipping_area area', 'item.area_id=area.id')
                ->where(['item.area' => $address['area'], 'item.town' => 0, 'area.enable' => 1])
                ->whereLike('area.com_codes', '%,' . $shipping_code . ',%')
                ->field('item.*,area.first_weight,money,second_weight,add_money')->find();
        }
        if (!$item) {
            $item = $areaItemModel->alias('item')->join('shipping_area area', 'item.area_id=area.id')
                ->where(['item.city' => $address['city'], 'item.area' => 0, 'area.enable' => 1])
                ->whereLike('area.com_codes', '%,' . $shipping_code . ',%')
                ->field('item.*,area.first_weight,money,second_weight,add_money')->find();
        }
        if (!$item) {
            $item = $areaItemModel->alias('item')->join('shipping_area area', 'item.area_id=area.id')
                ->where(['item.province' => $address['province'], 'item.city' => 0, 'area.enable' => 1])
                ->whereLike('area.com_codes', '%,' . $shipping_code . ',%')
                ->field('item.*,area.first_weight,money,second_weight,add_money')->find();
        }
        if (!$item) {
            $item = $areaItemModel->where(['item.province' => 0, 'area.enable' => 1])
                ->alias('item')->join('shipping_area area', 'item.area_id=area.id')
                ->whereLike('area.com_codes', '%,' . $shipping_code . ',%')
                ->field('item.*,area.first_weight,money,second_weight,add_money')->find();
        }
        if (!$item) {
            return ['code' => 0, 'msg' => '未找到匹配的物流规则', 'address' => $address];
        }

        if ($weight <= $item['first_weight']) {
            return ['code' => 1, 'msg' => 'ok', 'money' => $item['money'], 'area_config' => $item, 'shipping_com' => $shipping_com];
        }

        if ($item['second_weight'] <= 0 || $item['add_money'] <= 0) {
            return ['code' => 1, 'msg' => 'ok', 'money' => $item['money'], 'area_config' => $item, 'shipping_com' => $shipping_com];
        }

        $weight = $weight - $item['first_weight']; // 续重
        $weight = ceil($weight / $item['second_weight']); // 续重不够取整

        $money = $item['money'] + $weight * $item['add_money']; // 首重 + 续重 * 续重费

        return ['code' => 1, 'msg' => 'ok', 'money' => $money, 'area_config' => $item, 'shipping_com' => $shipping_com];
    }
}
