<?php

namespace app\admin\controller;

use app\common\logic\CouponLogic;
use app\common\model\ShopCouponList as CouponListModel;
use think\Controller;
use tpext\builder\traits\actions;

/**
 * Undocumented class
 * @title 优惠券
 */
class Shopcouponlist extends Controller
{
    use actions\HasIndex;
    use actions\HasBase;
    use actions\HasView;
    use actions\HasAdd;
    use actions\HasDelete;
    use actions\HasEnable;

    /**
     * Undocumented variable
     *
     * @var CouponListModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new CouponListModel;

        $this->pageTitle = '优惠券';
        $this->sortOrder = 'id desc';
        $this->enableField = 'status';
        $this->pagesize = 14;
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

        if (!$isEdit) {
            $form->select('coupon_type_id', '优惠券')->required()->dataUrl(url('/admin/shopcoupontype/selectPage'));
            $form->select('member_id', '会员')->required()->dataUrl(url('/admin/member/selectPage'));
            $form->number('num', '发送数量')->required()->default(1);
        }

        if ($isEdit) {
            $form->show('nickname ', '用户');
            $form->show('order_id', '订单id');
            $form->show('use_time ', '使用时间');
            $form->show('code', '优惠券兑换码');
            $form->show('create_time ', '发放时间');
            $form->show('get_time ', '领取日期');
            $form->match('status ', '可用状态')->options([0 => '禁用', 1 => '正常', 2 => '已使用']);
            $form->show('no_', '编号');
        }

        if ($isEdit && $data['order_id'] > 0) {
            $data['status'] = 2;
        }
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];

        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', '=', $searchData['member_id']];
        }

        if (!empty($searchData['order_id'])) {
            $where[] = ['card_type', '=', $searchData['card_type']];
        }
        if (!empty($searchData['no_'])) {
            $where[] = ['no_', 'like', '%,' . $searchData['no_'] . ',%'];
        }
        if (isset($searchData['coupon_type_id']) && $searchData['coupon_type_id'] != '') {
            $where[] = ['coupon_type_id', '=', $searchData['coupon_type_id']];
        }
        if (isset($searchData['is_use'])) {
            if ($searchData['is_use'] == '0') {
                $where[] = ['order_id', '=', 0];
            } else if ($searchData['is_use'] == '1') {
                $where[] = ['order_id', '>', 0];
            }
        }

        if (isset($searchData['status']) && $searchData['status'] != '') {
            $where[] = ['status', '=', $searchData['status']];
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
        $search->text('member_id', '会员id', 4);
        $search->text('order_id', '订单id', 4);
        $search->text('no_', '优惠券编号', 4);
        $search->select('is_use', '使用状态', 4)->options([0 => '未使用', 1 => '已使用']);
        $search->select('coupon_type_id', '类型', 4)->dataUrl('/admin/shopcoupontype/selectPage');
        $search->select('status', '可用状态', 4)->options([0 => '禁用', 1 => '正常']);
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
        $table->show('type_name', '优惠券');
        $table->match('card_type', '类型')->options([1 => '折扣券', 2 => '购物券']);
        $table->match('type', '发放方式')->options([0 => '面额模板', 1 => '按用户发放', 2 => '注册', 3 => '邀请', 4 => '线下发放']);
        $table->show('nickname ', '用户');
        $table->show('order_id', '订单id');
        $table->show('use_time ', '使用时间');
        $table->show('code', '优惠券兑换码');
        $table->show('create_time ', '发放时间')->getWrapper()->addStyle('width:160px;');
        $table->show('get_time ', '领取日期')->getWrapper()->addStyle('width:160px;');
        $table->match('status ', '可用状态')->options([0 => '禁用', 1 => '正常', 2 => '已使用'])->mapClassGroup([[0, 'danger'], [1, 'success']]);
        $table->show('no_', '编号');
        $table->sortable('id,card_type,get_time,send_time');

        foreach ($data as &$d) {
            $d['__hid_en__'] = $d['status'] == 1 || $d['order_id'] > 0;
            $d['__hid_dis__'] = $d['status'] == 0 || $d['order_id'] > 0;
            $d['__hid_del__'] =  $d['order_id'] > 0;
            if ($d['order_id'] > 0) {
                $d['status'] = 2;
            }
        }
        unset($d);

        $table->getActionbar()
            ->btnView()
            ->btnEnableAndDisable()
            ->btnDelete()
            ->mapClass([
                'enable' => ['hidden' => '__hid_en__'],
                'disable' => ['hidden' => '__hid_dis__'],
                'delete' => ['hidden' => '__hid_del__'],
            ]);
    }

    private function save($id = 0)
    {
        if ($id) {
            $this->error('禁止修改');
        }
        $data = request()->only([
            'coupon_type_id',
            'member_id',
            'num',
        ], 'post');

        $result = $this->validate($data, [
            'coupon_type_id|优惠券类型' => 'require|number',
            'member_id|会员' => 'require|number',
            'num|数量' => 'require|number',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        $logic = new CouponLogic;

        $res = $logic->create($data['member_id'], $data['coupon_type_id'], $data['num']);

        if ($res['code'] != 1) {
            $this->error($res['msg']);
        }

        return $this->builder()->layer()->closeRefresh(1, '发送成功');
    }
}
