<?php

namespace app\admin\controller;

use app\common\model\ShopOrder;
use app\common\model;
use think\Controller;
use tpext\builder\traits\actions;
use tpext\myadmin\admin\model\AdminUser;
use tpext\areacity\api\model\Areacity;

/**
 * Undocumented class
 * @title 商家发货单
 */
class Shopordershipping extends Controller
{
    use actions\HasIndex;
    use actions\HasBase;

    protected $currentAdmin;
    protected $goods_ids = [];

    protected function initialize()
    {
        $this->pageTitle = '商家发货单';
        $this->sortOrder = 'id desc';
        $this->pagesize = 14;

        $this->currentAdmin = AdminUser::current();
        $this->goods_ids = model\ShopGoods::where(['admin_group_id' => $this->currentAdmin['group_id']])->column('id');
    }

    /**
     * Undocumented function
     *
     * @return array|\think\Collection
     */
    protected function buildDataList()
    {
        $page = input('post.__page__/d', 1);
        $page = $page < 1 ? 1 : $page;

        $where = $this->filterWhere();


        $order_status_arr = [ShopOrder::ORDER_STATUS_0, ShopOrder::ORDER_STATUS_3, ShopOrder::ORDER_STATUS_5];

        if ($this->currentAdmin['id'] == 1 || $this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            //不限制
        } else {
            $where[] = ['g.goods_id', 'in', $this->goods_ids];
        }

        $where[] = ['o.pay_status', '=', 1];
        $where[] = ['o.order_status', 'not in', $order_status_arr];

        $table = $this->table;

        $pagesize = input('post.__pagesize__/d', 0);

        $this->pagesize = $pagesize ?: $this->pagesize;

        $total = db('shop_order_goods')->alias('g')
            ->join('shop_order o', 'g.order_id=o.id')
            ->where($where)->count();

        $data = db('shop_order_goods')->alias('g')
            ->join('shop_order o', 'g.order_id=o.id')
            ->where($where)
            ->order('o.shipping_status,o.pay_time desc')
            ->limit(($page - 1) * $this->pagesize, $this->pagesize)
            ->field('g.*,o.order_sn,o.order_status,o.pay_status,o.shipping_status,o.pay_time,o.consignee,'
                . 'o.consignee,o.mobile,o.address,o.province,o.city,o.area,o.town')->select();

        $this->buildTable($data);
        $table->fill($data);
        $table->paginator($total, $this->pagesize);
        $table->sortOrder('o.shipping_status desc');

        return $data;
    }

    private function getPcatAttr($data)
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


    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];

        if (isset($searchData['goods_id']) && $searchData['goods_id'] != '') {
            $where[] = ['g.goods_id', '=', $searchData['goods_id']];
        }

        if (!empty($searchData['order_sn'])) {
            $where[] = ['o.order_sn', 'like', "%{$searchData['order_sn']}%"];
        }
        if (!empty($searchData['consignee'])) {
            $where[] = ['o.consignee', 'like', '%' . $searchData['consignee'] . '%'];
        }
        if (!empty($searchData['mobile'])) {
            $where[] = ['o.mobile', '=', $searchData['mobile']];
        }
        if (!empty($searchData['address'])) {
            $where[] = ['o.address', 'like', '%' . $searchData['address'] . '%'];
        }
        if (isset($searchData['is_send']) && $searchData['is_send'] != '') {
            $where[] = ['g.is_send', '=', $searchData['is_send']];
        }
        if (!empty($searchData['province'])) {
            $where[] = ['o.province', '=', $searchData['province']];

            if (!empty($searchData['city'])) {
                $where[] = ['o.city', '=', $searchData['city']];

                if (!empty($searchData['area'])) {
                    $where[] = ['o.area', '=', $searchData['area']];
                }
            }
        }

        return $where;
    }

    /**
     * 构建搜索
     *
     * @return void
     */
    protected function buildSearch()
    {
        $search = $this->search;
        if ($this->currentAdmin['id'] == 1 || $this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            //不限制
            $search->select('goods_id', '产品')->optionsData(model\ShopGoods::select(), 'name');
        } else {
            $search->select('goods_id', '产品')->optionsData(model\ShopGoods::where('id', 'in', $this->goods_ids)->select(), 'name');
        }

        $search->text('order_sn', '订单sn');
        $search->text('consignee', '收货人')->maxlength(20);
        $search->text('mobile', '电话')->maxlength(20);
        $search->text('address', '收货地址')->maxlength(55);
        $search->select('is_send', '发货状态')->options([0 => '未发货', 1 => '已发货']);
        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;

        $table->show('order_id', '订单id');
        $table->show('order_sn', '订单sn');
        $table->show('consignee', '收货人');
        $table->show('mobile', '电话');
        $table->show('pcat', '省市区');
        $table->show('address', '详细地址');
        $table->show('goods_name', '产品名称');
        $table->show('spec_key_name', '规格')->default('--');
        $table->show('goods_num', '数量')->to('x {val}');
        $table->match('order_status', '订单状态')->options(ShopOrder::$order_status_types);
        $table->fields('pay_status', '支付状态')->with(
            $table->match('pay_status', '支付状态')->options(ShopOrder::$pay_status_types),
            $table->show('pay_time', '支付时间')
        );

        $table->match('is_send', '发货状态')->options([0 => '未发货', 1 => '已发货']);
        $table->getToolbar()->btnRefresh();

        foreach ($data as &$d) {
            $d['pcat'] = $this->getPcatAttr($d);
            $d['__hi_deliver__']  = $d['is_send'] == 1;
            $d['__hi_view__']  = $d['delivery_id'] == 0;
        }

        $table->getActionbar()
            ->btnLink('deliver', url('/admin/deliverylog/add', ['order_id' => '__data.order_id__']), '发货', 'btn-brown', '')
            ->btnView(url('/admin/deliverylog/view',['id'=>'__data.delivery_id__']))
            ->mapClass([
                'deliver' => ['hidden' => '__hi_deliver__'],
                'view' =>  ['hidden' => '__hi_view__'],
            ]);

        $table->useCheckbox(false);
    }
}
