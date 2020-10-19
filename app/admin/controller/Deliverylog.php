<?php

namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\common\model\DeliveryLog as DeliveryLogModel;
use app\common\model\ShippingCom;
use app\common\model\ShopOrder;
use app\common\model\ShopOrderGoods;
use think\Controller;
use tpext\builder\traits\actions;
use tpext\myadmin\admin\model\AdminUser;
use app\common\model;

/**
 * Undocumented class
 * @title 发货记录
 */
class Deliverylog extends Controller
{
    use actions\HasIndex;
    use actions\HasAdd;
    use actions\HasView;
    use actions\HasBase;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var DeliveryLogModel
     */
    protected $dataModel;

    /**
     * Undocumented variable
     *
     * @var ShopOrder
     */
    protected $orderModel;

    /**
     * Undocumented variable
     *
     * @var ShopOrderGoods
     */
    protected $ordreGoodsModel;

    /**
     * Undocumented variable
     *
     * @var ShippingCom
     */
    protected $shippingComModel;

    protected $currentAdmin;
    protected $goods_ids = [];

    protected function initialize()
    {
        $this->dataModel = new DeliveryLogModel;
        $this->orderModel = new ShopOrder;
        $this->ordreGoodsModel = new ShopOrderGoods;
        $this->shippingComModel = new ShippingCom;

        $this->pageTitle = '发货记录';
        $this->sortOrder = 'id desc';
        $this->pagesize = 14;

        $this->currentAdmin = AdminUser::current();
        $this->goods_ids = model\ShopGoods::where(['admin_group_id' => $this->currentAdmin['group_id']])->column('id');
    }

    /**
     * 构建表单
     *
     * @param boolean $isEdit
     * @param array $data
     */
    protected function buildForm($isEdit, &$data = [])
    {
        $form = $this->form;
        $order_id = input('order_id');

        $order = $this->orderModel->get($order_id);
        if (!$order) {
            return $this->builder()->layer()->closeRefresh(0, '订单不存在-' . $order_id);
        }

        $form->hidden('order_id')->value($order_id);

        $andWhere = [];

        if ($this->currentAdmin['id'] == 1 || $this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            //不限制
        } else {
            $andWhere[] = ['goods_id', 'in', $this->goods_ids];
        }

        $sendList = $this->ordreGoodsModel->where(['order_id' => $order_id, 'is_send' => 1])->where($andWhere)->select();
        $waitSendList = $this->ordreGoodsModel->where(['order_id' => $order_id, 'is_send' => 0])->where($andWhere)->select();

        if (count($sendList)) {
            $form->tab('继续发货');
        }

        if (count($waitSendList)) {
            $form->items('send_list', '待发货')->required()->dataWithId($waitSendList)->with(
                $form->show('goods_name', '商品'),
                $form->show('spec_key_name', '规格')->default('---'),
                $form->show('sale_price', '售价'),
                $form->show('member_price', '会员价格'),
                $form->show('goods_num', '数量')->to('x {val}'),
                $form->checkbox('send', '发货')->options([1 => '是'])->default(1),
            )->canAdd(false)->cnaDelete(false)->size(12, 12)
                ->help('同一个订单多个产品时，可以选择部分产品分多次发货');
        }

        $form->select('shipping_code', '物流公司')->dataUrl(url('/admin/shippingcom/selectPage'), 'name', 'code')->required();
            //->help('默认选中的是用户提交订单时选择的，和用户协商一致后可修改。若更改物流公司导致运费改变，请自行协商补差价。')->required();

        if (count($waitSendList)) {
            $form->text('invoice_no', '物流单号')->maxlength(55)->required();
            $form->textarea('note', '备注信息')->maxlength(55);
        } else {
            $form->show('success', '所有货物已发货');
        }

        $data['shipping_code'] = $order['shipping_code'];

        if (count($sendList)) {
            $form->tab('已发货');
            $form->items('send_list', '已发货')->dataWithId($sendList)->with(
                $form->show('goods_name', '商品'),
                $form->show('spec_key_name', '规格')->default('---'),
                $form->show('sale_price', '售价'),
                $form->show('member_price', '会员价格'),
                $form->show('goods_num', '数量')->to('x {val}')
            )->canAdd(false)->cnaDelete(false)->size(12, 12);
        }
    }

    public function view($id)
    {
        $builder = $this->builder($this->pageTitle, $this->viewText);

        $data = $this->dataModel->get($id);
        if (!$data) {
            return $builder->layer()->close(0, '数据不存在');
        }

        $form = $builder->form();

        $andWhere = [];

        if ($this->currentAdmin['id'] == 1 || $this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            //不限制
        } else {
            $andWhere[] = ['goods_id', 'in', $this->goods_ids];
        }

        $sendList = $this->ordreGoodsModel->where(['order_id' => $data['order_id'], 'is_send' => 1, 'delivery_id' => $id])->where($andWhere)->select();

        $form->items('send_list', '本次发货列表')->dataWithId($sendList)->with(
            $form->show('goods_name', '商品'),
            $form->show('spec_key_name', '规格')->default('---'),
            $form->show('sale_price', '售价'),
            $form->show('member_price', '会员价格'),
            $form->show('goods_num', '数量')->to('x {val}')
        )->canAdd(false)->cnaDelete(false)->size(12, 12);

        $form->show('shipping_name', '物流公司');
        $form->show('invoice_no', '物流单号');
        $form->show('order_sn', '订单sn');
        $form->show('nickname', '会员');
        $form->show('consignee', '收货人');
        $form->show('mobile', '手机号');
        $form->show('pcat', '地区');
        $form->show('address', '详细地址');
        $form->show('shipping_price', '运费');

        $form->show('note', '备注信息')->default('-空-');
        $form->show('create_time', '操作时间');
        $form->fill($data);

        $form->readonly();

        return $builder->render();
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];

        if ($this->currentAdmin['id'] == 1 || $this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            //不限制
        } else {
            $ids = ShopOrderGoods::where('goods_id', 'in', $this->goods_ids)->column('delivery_id');
            $where[] = ['id', 'in',  $ids];
        }
        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', 'eq', $searchData['member_id']];
        }
        if (!empty($searchData['order_sn'])) {
            $where[] = ['order_sn', 'like', '%' . $searchData['order_sn'] . '%'];
        }
        if (!empty($searchData['consignee'])) {
            $where[] = ['consignee', 'like', '%' . $searchData['consignee'] . '%'];
        }
        if (!empty($searchData['mobile'])) {
            $where[] = ['mobile', 'like', '%' . $searchData['mobile'] . '%'];
        }
        if (!empty($searchData['address'])) {
            $where[] = ['address', 'like', '%' . $searchData['address'] . '%'];
        }
        if (!empty($searchData['province'])) {
            $where[] = ['province', 'eq', $searchData['province']];

            if (!empty($searchData['city'])) {
                $where[] = ['city', 'eq', $searchData['city']];

                if (!empty($searchData['area'])) {
                    $where[] = ['area', 'eq', $searchData['area']];

                    if (!empty($searchData['town'])) {
                        $where[] = ['town', 'eq', $searchData['town']];
                    }
                }
            }
        }

        if (!empty($searchData['start'])) {
            $where[] = ['create_time', 'egt', $searchData['start']];
        }

        if (!empty($searchData['end'])) {
            $where[] = ['create_time', 'elt', $searchData['end']];
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
        $search->text('member_id', '会员id');
        $search->text('order_sn', '订单sn')->maxlength(30);
        $search->text('consignee', '收货人')->maxlength(20);
        $search->text('mobile', '电话')->maxlength(20);
        $search->text('address', '地址');
        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')->withNext(
                    $search->select('town', '乡镇')->dataUrl(url('api/areacity/town'), 'ext_name')
                )
            )
        );

        $search->datetime('start ', '操作时间', 3)->placeholder('起始');
        $search->datetime('end ', '~', 3)->placeholder('截止');
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;
        $table->show('id', 'ID');
        $table->show('order_id', '订单ID');
        $table->show('order_sn', '订单sn');
        $table->show('nickname', '会员');
        $table->show('consignee', '收货人');
        $table->show('mobile', '手机号');
        $table->show('pcat', '地区');
        $table->show('address', '详细地址');
        $table->text('shipping_code', '物流code')->autoPost();
        $table->text('shipping_name', '快递名称')->autoPost();
        $table->show('shipping_price', '运费');
        $table->text('invoice_no', '物流单号')->autoPost();
        $table->show('note', '备注信息');
        $table->show('create_time', '操作时间');

        $table->sortable('id,shipping_code,shipping_name,invoice_no');

        $table->getToolbar()
            ->btnRefresh()
            ->btnToggleSearch();
        //->btnImport(url('/admin/shoporderaction/importShipping'), 'xls,xlsx', ['800px', '550px'], 20, '导入发货单')
        //->html('<a class="label label-info" target="_blank" href="/template/发货订单模板.xls">发货单模板下载</a>');;

        $table->getActionbar()
            ->btnView();
    }

    private function save($id = 0)
    {
        $order_id = input('order_id');

        $order = $this->orderModel->get($order_id);
        if (!$order) {
            return $this->builder()->layer()->closeRefresh(0, '订单不存在-' . $order_id);
        }

        $data = request()->only([
            'order_id',
            'shipping_code',
            'invoice_no',
            'send_list',
            'note',
        ], 'post');

        $result = $this->validate($data, [
            'order_id|订单编号' => 'require|number',
            'shipping_code|物流公司' => 'require',
            'invoice_no|物流单号' => 'require',
            'send_list|产品选择' => 'require|array',
        ]);

        if (true !== $result) {
            $this->error($result);
        }

        $waitSendList = $this->ordreGoodsModel->where(['order_id' => $order_id, 'is_send' => 0])->select();

        if (!count($waitSendList)) {
            $this->error('无待发货产品！');
        }

        $send_list = $data['send_list'];

        unset($data['send_list']);

        $goods_ids = [];
        foreach ($send_list as $key => $sd) {
            if (isset($sd['send']) && $sd['send'] = 1) { //选中
                $goods_ids[] = $key;
            }
        }

        if (empty($goods_ids)) {
            $this->error('未选中产品');
        }

        $orderLogic = new OrderLogic;

        $res = $orderLogic->delivery($order_id, $data, $goods_ids);

        if ($res['code'] != 1) {
            $this->error('操作失败-' . $res['msg']);
        }

        return $this->builder()->layer()->closeRefresh(1, $res['msg']);
    }
}
