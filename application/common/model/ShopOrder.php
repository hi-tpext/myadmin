<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use tpext\areacity\api\model\Areacity;

class ShopOrder extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'dateTime';

    /**
     * 待确认
     */
    public const ORDER_STATUS_0 = 0;
    /**
     * 已确认
     */
    public const ORDER_STATUS_1 = 1;
    /**
     *已收货
     */
    public const ORDER_STATUS_2 = 2;
    /**
     * 已取消
     */
    public const ORDER_STATUS_3 = 3;
    /**
     * 已完成
     */
    public const ORDER_STATUS_4 = 4;
    /**
     * 已作废
     */
    public const ORDER_STATUS_5 = 5;

    /**
     * 未发货
     */
    public const SHIPPING_STATUS_0 = 0;
    /**
     * 已发货
     */
    public const SHIPPING_STATUS_1 = 1;
    /**
     * 部分发货
     */
    public const SHIPPING_STATUS_2 = 2;

    /**
     * 未支付
     */
    public const PAY_STATUS_0 = 0;
    /**
     * 已支付
     */
    public const PAY_STATUS_1 = 1;
    /**
     * 申请退款
     */
    public const PAY_STATUS_2 = 2;
    /**
     * 退款通过
     */
    public const PAY_STATUS_3 = 3;
    /**
     * 退款未通过
     */
    public const PAY_STATUS_4 = 4;

    public const PROM_TYPE_1 = 1;
    public const PROM_TYPE_2 = 2;
    public const PROM_TYPE_3 = 3;
    public const PROM_TYPE_4 = 4;
    public const PROM_TYPE_5 = 5;

    public const PAY_CODE_WX_PAY = 'wxpay';
    public const PAY_CODE_ALI_PAY = 'alipay';
    public const PAY_CODE_COD = 'cod';
    public const PAY_CODE_OTHER = 'other';

    public const ORDER_STATUS_DESC_WAITPAY = 'WAITPAY';
    public const ORDER_STATUS_DESC_WAITSEND = 'WAITSEND';
    public const ORDER_STATUS_DESC_WAITRECEIVE = 'WAITRECEIVE';
    public const ORDER_STATUS_DESC_WAITCCOMMENT = 'WAITCCOMMENT';
    public const ORDER_STATUS_DESC_CANCEL = 'CANCEL';
    public const ORDER_STATUS_DESC_FINISH = 'FINISH';
    public const ORDER_STATUS_DESC_CANCELLED = 'CANCELLED';

    public static $order_status_types = [
        self::ORDER_STATUS_0 => '待确认',
        self::ORDER_STATUS_1 => '已确认',
        self::ORDER_STATUS_2 => '已收货',
        self::ORDER_STATUS_3 => '已取消',
        self::ORDER_STATUS_4 => '已完成',
        self::ORDER_STATUS_5 => '已作废',
    ];

    public static $shipping_status_types = [
        self::SHIPPING_STATUS_0 => '未发货',
        self::SHIPPING_STATUS_1 => '已发货',
        self::SHIPPING_STATUS_2 => '部分发货',
    ];

    public static $pay_status_types = [
        self::PAY_STATUS_0 => '未支付',
        self::PAY_STATUS_1 => '已支付',
        self::PAY_STATUS_2 => '申请退款',
        self::PAY_STATUS_3 => '退款通过',
        self::PAY_STATUS_4 => '退款未通过',
    ];

    public static $prom_types = [
        self::PROM_TYPE_1 => '默认',
        self::PROM_TYPE_2 => '抢购',
        self::PROM_TYPE_3 => '团购',
        self::PROM_TYPE_4 => '优惠',
        self::PROM_TYPE_5 => '砍价',
    ];

    public static $pay_codes = [
        self::PAY_CODE_WX_PAY => '微信支付',
        self::PAY_CODE_ALI_PAY => '支付宝',
        self::PAY_CODE_COD => '货到付款',
        self::PAY_CODE_OTHER => '其他',
    ];

    public static $orders_status_desc = [
        self::ORDER_STATUS_DESC_WAITPAY => '待支付',
        self::ORDER_STATUS_DESC_WAITSEND => '待发货',
        self::ORDER_STATUS_DESC_WAITRECEIVE => '待收货',
        self::ORDER_STATUS_DESC_WAITCCOMMENT => '待评价',
        self::ORDER_STATUS_DESC_CANCEL => '已取消',
        self::ORDER_STATUS_DESC_FINISH => '已完成',
        self::ORDER_STATUS_DESC_CANCELLED => '已作废',
    ];

    protected static function init()
    {
        self::afterDelete(function ($data) {
            //ShopOrderGoods::where(['order_id' => $data['id'], 'is_send' => 0])->delete();
        });
    }

    public function getNicknameAttr($value, $data)
    {
        $member = Member::get($data['member_id']);
        return $member ? $member['nickname'] : '会员不存在';
    }

    public function getPcatAttr($value, $data)
    {
        $text = '---';

        $province = Areacity::where(['id' => $data['province']])->find();

        if ($province) {

            $text = $province['ext_name'];
            $city = Areacity::where(['id' => $data['city']])->find();

            if ($city) {

                $text .= ',' . $city['ext_name'];
                $area = Areacity::where(['id' => $data['area']])->find();

                if ($area) {

                    $text .= ',' . $area['ext_name'];

                    $town = Areacity::where(['id' => $data['town']])->find();

                    if ($town) {

                        $text .= ',' . $town['ext_name'];
                    }
                }
            }
        }

        return $text;
    }

    public function getGoodsNamesAttr($value, $data)
    {
        $goods = ShopOrderGoods::where(['order_id' => $data['id']])->field('goods_name,spec_key_name,goods_num')->select();
        $names = [];
        foreach ($goods as $g) {
            $names[] = $g['goods_name'] . ($g['spec_key_name'] ? '(' . $g['spec_key_name'] . ')' : '') . ' X ' . $g['goods_num'];
        }
        return  implode('、', $names);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'id', 'member_id');
    }
}
