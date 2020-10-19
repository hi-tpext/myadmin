<?php

namespace app\admin\controller;

use app\admin\logic\OrderLogic as AdminOrderLogic;
use app\common\logic\GoodsLogic;
use app\common\logic\OrderLogic;
use app\common\model;
use app\common\model\MemberAccount;
use app\common\model\ShopOrder as OrderModel;
use think\Controller;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 商城订单
 */
class Shoporder extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var OrderModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new OrderModel;

        $this->pageTitle = '商城订单';
        $this->sortOrder = 'id desc';
        $this->pagesize = 8;

        $this->selectSearch = 'order_sn|mobile';
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

        $form->defaultDisplayerSize(12, 12);
        $member = null;

        $goodsLogic = new GoodsLogic;
        $adminOrderLogic = new AdminOrderLogic;
        $specList = [];
        $orderGoodsList = [];

        if ($isEdit) {
            $form->tab('订单信息');

            $form->show('id', '编号', 4);
            $form->show('order_sn', '订单sn', 4);
            $form->show('nickname', '会员', 4);

            $orderGoodsList = model\ShopOrderGoods::where(['order_id' => $data['id']])->select();
            foreach ($orderGoodsList as &$orderGoods) {
                if ($orderGoods['spec_key'] == '') {
                    $orderGoods['spec_key'] = 'g_' . $orderGoods['goods_id'];
                }
            }
            $member = model\Member::get($data['member_id']);

            $specList = $goodsLogic->getSpecList();
        }

        if ($isEdit == 0) {
            $form->select('address_id', '会员&收货地址', 8)->dataUrl(url('/admin/memberaddress/selectPage'))->required();
        } else if ($isEdit == 1) {
            $list = $adminOrderLogic->getAddressList($data['member_id']);
            $form->select('address_id', '货地址', 8)->optionsData($list, 'text')->required();
        } else {
            $address = '收货人:' . $data['consignee'] . ',手机:' . $data['mobile'] . ',地址:' . $data['pcat'] . ',' . $data['address'];
            $form->show('address_text', '收货地址', 8)->value($address);
        }

        if ($isEdit == 0 || $isEdit == 1) {
            $form->select('shipping_code', '物流方式', 4)->dataUrl(url('/admin/shippingcom/selectPage'), 'name', 'code')->help('从已启用的快递公司总选择一个')->required();
        } else {
            $form->show('shipping_name', '物流方式', 4);
        }

        if ($isEdit == 0 || $isEdit == 1) {
            $form->items('goods_list', '产品列表')->required()->dataWithId($orderGoodsList)->with(
                $form->select('spec_key', '产品＆规格')->optionsData($specList, 'text')->dataUrl(url('/admin/shopgoods/specList'))->required()->getWrapper()->addStyle('text-align:left;'),
                $form->number('goods_num', '数量')->default(1)->required()->min(1)
            );
        } else {
            $form->items('goods_list', '产品列表')->dataWithId($orderGoodsList)->with(
                $form->show('goods_name', '产品名称'),
                $form->show('goods_sn', '产品sn'),
                $form->show('goods_sku', '产品sku'),
                $form->show('spec_key_name', '规格'),
                $form->show('sale_price', '销售价'),
                $form->show('member_price', '会员价'),
                $form->show('goods_num', '数量')->to('x {val}'),
                $form->show('weight', '重量')->to('{val}克'),
                $form->match('is_send', '发货')->options([0 => '未发货', 1 => '已发货'])
            );
        }

        if ($isEdit) {
            $form->show('goods_price', '产品总价', 4);
            $form->show('shipping_price', '邮费', 4);
            $form->show('total_amount', '订单总价', 4);
            $form->show('coupon_price', '提货券抵扣', 4);
        }

        $form->text('discount', '价格调整', 4)->default(0)->help('输入正数或负数');
        $form->text('use_money', '使用' . MemberAccount::$types['money'], 4)->default(0)->help($isEdit && $member ? '可用:' . $member['money'] : '');
        $form->text('use_points', '使用' . MemberAccount::$types['points'], 4)->default(0)->help($isEdit && $member ? '可用:' . $member['points'] : '');
        $form->text('use_commission', '使用' . MemberAccount::$types['commission'], 4)->default(0)->help($isEdit && $member ? '可用:' . $member['commission'] : '');

        if ($isEdit) {
            $form->show('order_amount', '应付款金额', 4);
            if ($data['coupon_price'] > 0) {
                $clist = model\ShopCouponList::where(['order_id' => $data['id']])->select();
                $form->items('coupon_list', '已使用提货券')->dataWithId($clist)->with(
                    $form->show('id', 'id'),
                    $form->show('nickname', '领取会员'),
                    $form->show('code', '优惠券兑换码'),
                    $form->show('create_time ', '发放时间')->getWrapper()->addStyle('width:160px;'),
                    $form->show('get_time ', '领取日期')->getWrapper()->addStyle('width:160px;'),
                    $form->show('no_', '编号')
                )->canAdd(false)->cnaDelete(false)->size(12, 12);
            }
        }

        $form->html('');

        if ($isEdit) {

            $form->match('order_status', '订单状态', 4)->options(OrderModel::$order_status_types);
            $form->match('pay_status', '支付状态', 4)->options(OrderModel::$pay_status_types);
            if ($data['pay_status'] != 0) {
                $form->match('pay_time', '支付时间', 3)->default('---');
                $form->match('pay_code', '支付方式', 3)->options(OrderModel::$pay_codes)->afterOptions(['__default__' => '---']);
                $form->show('transaction_id', '支付流水号', 3)->default('---');
            }

            $form->match('shipping_status', '物流状态', 4)->options(OrderModel::$shipping_status_types);
            if ($data['shipping_status'] != 0) {
                $form->show('shipping_time', '发货时间', 3)->default('---');
                $form->show('shipping_time', '发货时间', 3)->default('---');
                $form->show('confirm_time', '收货时间', 3)->default('---');
            }

            $form->show('create_time', '添加时间', 3);
            $form->show('update_time', '修改时间', 3);
        }

        $form->textarea('user_note', '会员备注');
        $form->textarea('admin_note', '管理员备注');

        if ($isEdit == 1) {
            $logList = model\ShopOrderAction::where(['order_id' => $data['id']])->order('id desc')->select();
            $form->tab('操作日志');
            $form->items('log_list', ' ')->dataWithId($logList)->with(
                $form->show('action_note', '操作备注'),
                $form->show('status_desc', '描述'),
                $form->match('order_status', '订单状态')->options(OrderModel::$order_status_types),
                $form->match('pay_status', '支付状态')->options(OrderModel::$pay_status_types),
                $form->match('shipping_status', '物流状态')->options(OrderModel::$shipping_status_types),
                $form->show('create_time', '时间')
            )->canAdd(false)->cnaDelete(false)->size(0, 12)->showLabel(false);
        }
    }

    protected function filterWhere()
    {
        $searchData = request()->isGet() ? request()->param() : request()->post();

        $where = [];

        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', 'eq', $searchData['member_id']];
        }
        if (!empty($searchData['order_sn'])) {
            $where[] = ['order_sn', 'like', '%' . $searchData['order_sn'] . '%'];
        }
        if (!empty($searchData['transaction_id'])) {
            $where[] = ['transaction_id', 'like', '%' . $searchData['transaction_id'] . '%'];
        }
        if (!empty($searchData['consignee'])) {
            $where[] = ['consignee', 'like', '%' . $searchData['consignee'] . '%'];
        }
        if (!empty($searchData['mobile'])) {
            $where[] = ['mobile', 'eq', $searchData['mobile']];
        }
        if (!empty($searchData['address'])) {
            $where[] = ['address', 'like', '%' . $searchData['address'] . '%'];
        }
        if (isset($searchData['order_status']) && $searchData['order_status'] != '') {
            $where[] = ['order_status', 'eq', $searchData['order_status']];
        }
        if (isset($searchData['pay_status']) && $searchData['pay_status'] != '') {
            $where[] = ['pay_status', 'eq', $searchData['pay_status']];
        }
        if (isset($searchData['shipping_status']) && $searchData['shipping_status'] != '') {
            $where[] = ['shipping_status', 'eq', $searchData['shipping_status']];
        }
        if (!empty($searchData['pay_name'])) {
            $where[] = ['pay_name', 'eq', $searchData['pay_name']];
        }
        if (!empty($searchData['province'])) {
            $where[] = ['province', 'eq', $searchData['province']];

            if (!empty($searchData['city'])) {
                $where[] = ['city', 'eq', $searchData['city']];

                if (!empty($searchData['area'])) {
                    $where[] = ['area', 'eq', $searchData['area']];
                }
            }
        }
        if (!empty($searchData['start'])) {
            $where[] = ['pay_time', 'egt', $searchData['start']];
        }

        if (!empty($searchData['end'])) {
            $where[] = ['pay_time', 'elt', $searchData['end']];
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
        $search->text('transaction_id', '支付流水号')->maxlength(55);
        $search->text('consignee', '收货人')->maxlength(20);
        $search->text('mobile', '电话')->maxlength(20);
        $search->text('address', '收货地址')->maxlength(55);
        $search->select('order_status', '订单状态')->options(OrderModel::$order_status_types)->default(input('order_status'));
        $search->select('pay_status', '支付状态')->options(OrderModel::$pay_status_types)->default(input('pay_status'));
        $search->select('shipping_status', '物流状态')->options(OrderModel::$shipping_status_types)->default(input('shipping_status'));
        $search->select('pay_name', '支付方式')->options(OrderModel::$pay_codes);

        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );

        $search->datetime('start', '支付时间', 3)->placeholder('起始');
        $search->datetime('end', '~', 3)->placeholder('截止');
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
        $table->fields('order_sn', '订单sn/支付流水号')->with(
            $table->show('order_sn', '订单sn'),
            $table->show('transaction_id', '支付流水号')->default('--')
        );
        $table->fields('nickname', '会员')->with(
            $table->show('nickname', '昵称'),
            $table->show('member_id', '会员id')
        );
        $table->fields('consignee', '收货人/电话')->with(
            $table->show('consignee', '收货人'),
            $table->show('mobile', '电话')->default('--')
        );
        $table->fields('address_info', '收货地址')->with(
            $table->show('pcat', '省市区'),
            $table->show('address', '详细地址')->addClass('hidden')
        );
        $table->show('goods_names', '产品');
        $table->fields('fee', '总价/邮费/合计')->with(
            $table->show('goods_price', '商品总价'),
            $table->show('shipping_price', '邮费'),
            $table->show('total_amount', '合计总价')
        );
        $table->fields('cut_money', MemberAccount::$types['money'] . '/' . MemberAccount::$types['points'] . '/' . MemberAccount::$types['commission'] . '/提货券')->with(
            $table->field('use_money', MemberAccount::$types['money'])->to('{val} / '),
            $table->field('points_money', MemberAccount::$types['points'])->to('{val} / '),
            $table->field('use_commission', MemberAccount::$types['commission'])->to('{val} / '),
            $table->field('coupon_price', '提货券抵扣')
        );
        $table->show('discount', '价格调整');
        $table->show('order_amount', '应付');
        $table->match('order_status', '订单状态')->options(OrderModel::$order_status_types);
        $table->fields('pay_status', '支付状态/时间')->with(
            $table->match('pay_status', '支付状态')->options(OrderModel::$pay_status_types)->mapClassWhenGroup([[1, 'success'], [2, 'danger']]),
            $table->show('pay_time', '支付时间')->default('--')
        );
        $table->match('pay_code', '支付方式')->options(OrderModel::$pay_codes)->afterOptions(['__default__' => '---']);
        $table->fields('shipping_status', '物流/状态/时间')->with(
            $table->show('shipping_name', '物流名称'),
            $table->match('shipping_status', '物流状态')->options(OrderModel::$shipping_status_types)->mapClassWhenGroup([[1, 'success'], [2, 'info']]),
            $table->show('shipping_time', '发货时间')->default('--')
        );
        $table->sortable('id,order_status,pay_status,shipping_status');

        foreach ($data as &$d) {
            $d['__hi_edit__'] = $d['pay_status'] != OrderModel::PAY_STATUS_0
            || !in_array($d['order_status'], [OrderModel::ORDER_STATUS_0, OrderModel::ORDER_STATUS_1]);

            $d['__hi_del__'] = !in_array($d['order_status'], [OrderModel::ORDER_STATUS_3, OrderModel::ORDER_STATUS_5])
            || $d['pay_status'] == OrderModel::PAY_STATUS_1 || $d['shipping_status'] != OrderModel::SHIPPING_STATUS_0;

            $d['__hi_deliver__'] = $d['order_status'] != OrderModel::ORDER_STATUS_1
            || $d['pay_status'] != OrderModel::PAY_STATUS_1 || $d['shipping_status'] == OrderModel::SHIPPING_STATUS_1;

            //$ps == 0 && $ss == 0 && ($os == 1 || $os == 0
            $d['__hi_order_cancel__'] = !($d['order_status'] <= OrderModel::ORDER_STATUS_1
                && $d['pay_status'] == OrderModel::PAY_STATUS_0 && $d['shipping_status'] == OrderModel::SHIPPING_STATUS_0);
        }

        unset($d);

        $table->useCheckbox(false);

        $table->getToolbar()
            ->btnAdd()
            ->btnRefresh()
            ->btnToggleSearch();
        //->btnImport(url('/admin/shoporderaction/importShipping'), 'xls,xlsx', ['800px', '550px'], 20, '导入发货单')
        //->html('<a class="label label-info" target="_blank" href="/template/发货订单模板.xls">发货单模板下载</a>');

        $table->getActionbar()
            ->btnView()
            ->btnEdit()
            ->btnDelete()
            ->btnLink('deliver', url('/admin/deliverylog/add', ['order_id' => '__data.pk__']), '发货', 'btn-brown', '')
            ->btnPostRowid('order_cancel', url('/admin/shoporderaction/orderCancel'), '取消', 'btn-default', '')
            ->mapClass([
                'edit' => ['hidden' => '__hi_edit__'],
                'delete' => ['hidden' => '__hi_del__'],
                'deliver' => ['hidden' => '__hi_deliver__'],
                'order_cancel' => ['hidden' => '__hi_order_cancel__'],
            ]);
    }

    public function view($id)
    {
        if (request()->isPost()) {
            $this->error('不允许的操作');
        } else {
            $builder = $this->builder($this->pageTitle, $this->viewText);
            $data = $this->dataModel->get($id);
            if (!$data) {
                return $builder->layer()->close(0, '数据不存在');
            }
            $form = $builder->form();
            $this->form = $form;

            $this->buildForm(2, $data);

            $rows = $this->form->getRows();

            $form->fill($data);

            $this->turn($rows);

            $form->readonly();

            $adminOrderLogic = new AdminOrderLogic;

            $button = $adminOrderLogic->getAdminButton($data);

            if (count($button)) {

                $form->readonly(false);

                $form->textarea('remark', '操作备注');
                $form->fields('')->showLabel(false)->size(0, 12);

                $btnStyles = [
                    'pay' => 'btn-success',
                    'confirm' => 'btn-cyan',
                    'delivery' => 'btn-brown',
                    'cancel' => 'btn-pink',
                    'pay_cancel' => 'btn-warning',
                    'delivery_confirm' => 'btn-primary',
                    'refund' => 'btn-info',
                    'remove' => 'btn-danger',
                    'invalid' => 'btn-dark',
                ];

                $form->html('', '', 2)->value('订单操作：');

                foreach ($button as $key => $text) {
                    $style = isset($btnStyles[$key]) ? $btnStyles[$key] : 'btn-secondary';
                    $form->button('button', $text)->class($style)->addClass('btn-xs order-action-btn')
                        ->attr('data-key="' . $key . '"');
                }

                $form->fieldsEnd();

                $deliveryUrl = url('/admin/deliverylog/add', ['order_id' => $id]);
                $payCancelUrl = url('/admin/shoporderaction/payCancel', ['id' => $id]);
                $actionUrl = url('/admin/shoporderaction/orderAction', ['id' => $id]);

                $script = <<<EOT

                $('.order-action-btn').click(function(){
                    var key = $(this).data('key');
                    var id = '{$id}';
                    if(key == 'delivery')
                    {
                        location.replace('{$deliveryUrl}')
                        return;
                    }
                    if(key == 'pay_cancel')
                    {
                        location.replace('{$payCancelUrl}')
                        return;
                    }
                    var remark = $('.row-remark').val();
                    var text = $(this).text().trim();

                    $.alert({
                        title: '操作提示',
                        content: '确定要执行【' + text + '】操作？',
                        buttons: {
                            confirm: {
                                text: '确认',
                                btnClass: 'btn-primary',
                                action: function () {
                                    lightyear.loading('show');
                                    $.ajax({
                                        url: '{$actionUrl}',
                                        data: {remark : remark , action : key, text : text},
                                        type: 'POST',
                                        dataType: 'json',
                                        success: function (data) {
                                            lightyear.loading('hide');
                                            if(data.code ==1)
                                            {
                                                parent.$(".search-refresh").trigger("click");
                                                lightyear.notify(data.msg || data.message || '操作成功！', 'success');
                                                setTimeout(function(){
                                                    if(key == 'remove')
                                                    {
                                                        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                                        parent.layer.close(index);
                                                    }
                                                    else
                                                    {
                                                        location.replace(location.href);
                                                    }
                                                },500);
                                            }
                                            else
                                            {
                                                lightyear.notify(data.msg || '操作失败！', 'danger');
                                            }
                                        },
                                        error:function(){
                                            lightyear.loading('hide');
                                            lightyear.notify('网络错误', 'danger');
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: '取消',
                                action: function () {

                                }
                            }
                        }
                    });
                });

EOT;

                $this->builder()->addScript($script);
            }

            $form->bottomOffset(5);
            $form->btnLayerClose('返&nbsp;&nbsp;回', 2);
            $form->bottomButtons(false);

            $logList = model\ShopOrderAction::where(['order_id' => $data['id']])->order('id desc')->select();
            $form->tab('操作日志');
            $form->items('log_list', ' ')->dataWithId($logList)->with(
                $form->show('action_note', '操作备注'),
                $form->show('status_desc', '描述'),
                $form->match('order_status', '订单状态')->options(OrderModel::$order_status_types),
                $form->match('pay_status', '支付状态')->options(OrderModel::$pay_status_types),
                $form->match('shipping_status', '物流状态')->options(OrderModel::$shipping_status_types),
                $form->show('create_time', '时间')
            )->canAdd(false)->cnaDelete(false)->size(0, 12)->showLabel(false);

            return $builder->render();
        }
    }

    private function save($id = 0)
    {
        $data = request()->only([
            'address_id',
            'goods_list',
            'shipping_code',
            'use_money',
            'use_points',
            'use_commission',
            'user_note',
            'admin_note',
            'discount',
        ], 'post');
        $result = $this->validate($data, [
            'goods_list|产品' => 'require|array',
            'address_id|收货地址' => 'number',
            'shipping_code|物流code' => 'require',
            'use_money|使用余额' => 'require|float|egt:0',
            'use_points|使用花豆' => 'require|float|egt:0',
            'use_commission|使用蜜豆' => 'require|float|egt:0',
            'discount|减免' => 'float',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($id) {
            $data['id'] = $id;
        }

        if ($id) {
            $order = $this->dataModel->get($id);
            if (!$order) {
                $this->error('订单数据不存在');
            }

            if ($order['pay_status'] != 0) {
                $this->error('不允许操作');
            }

            $data['member_id'] = $order['member_id'];
            $data['order_sn'] = $order['order_sn'];
        } else {
            $address = model\MemberAddress::get($data['address_id']);
            if (!$address) {
                $this->error('地址不存在');
            }
            $data['member_id'] = $address['member_id'];
        }

        $list = $data['goods_list'];
        $goods_list = [];

        foreach ($list as $key => $li) {
            if (preg_match('/^g_(\d+)$/i', $li['spec_key'], $mch)) {
                $li['spec_key'] = '';
                $li['goods_id'] = $mch[1];
            } else {
                $price = model\ShopGoodsSpecPrice::where(['spec_key' => $li['spec_key']])->find();
                if (!$price) {
                    $this->error('规格信息已不存在-' . $li['goods_name'] . ':' . $li['spec_key_name']);
                }
                $li['goods_id'] = $price['goods_id'];
            }

            $goods_list[] = [
                'goods_id' => $li['goods_id'],
                'goods_num' => $li['goods_num'],
                'spec_key' => $li['spec_key'],
                'order_goods_id' => $key,
                'is_del' => isset($li['__del__']) && $li['__del__'] == 1,
                'is_add' => strpos($key, '__new__') !== false,
            ];
        }
        
        $data['use_coupon_num'] = 0;
        $data['goods_list'] = $goods_list;
        $logic = new OrderLogic($data['member_id']);
        $res = $logic->create($data);

        if ($res['code'] != 1) {
            $this->error('保存失败-' . $res['msg']);
        }

        return $this->builder()->layer()->closeRefresh(1, '保存成功');
    }
}
