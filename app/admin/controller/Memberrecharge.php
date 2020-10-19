<?php

namespace app\admin\controller;

use app\common\logic\PaymentLogic;
use app\common\logic\RechargeLogic;
use app\common\model\Member;
use app\common\model\MemberRecharge as RechargeModel;
use think\Controller;
use tpext\builder\traits\actions;
use tpext\common\model\WebConfig;

/**
 * Undocumented class
 * @title 充值记录
 */
class Memberrecharge extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;
    use actions\HasAdd;
    use actions\HasView;

    /**
     * Undocumented variable
     *
     * @var RechargeModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new RechargeModel;
        $this->pageTitle = '充值记录';
        $this->pagesize = 10;
    }

    protected function filterWhere()
    {
        $searchData = request()->param();

        $where = [];
        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', 'eq', $searchData['member_id']];
        }

        if (!empty($searchData['order_sn'])) {
            $where[] = ['order_sn', 'like', '%' . $searchData['order_sn'] . '%'];
        }

        if (!empty($searchData['first_leader'])) {
            $where[] = ['first_leader', 'eq', $searchData['first_leader']];
        }

        if (!empty($searchData['leader_id'])) {
            $member_ids = Member::where('relation', 'like', '%,' . $searchData['leader_id'] . ',%')->column('id');
            $where[] = ['member_id', 'in', $member_ids];
        }

        if (isset($searchData['pay_status']) && $searchData['pay_status'] != '') {
            $where[] = ['pay_status', 'eq', $searchData['pay_status']];
        }

        if (isset($searchData['pay_code']) && $searchData['pay_code'] != '') {
            $where[] = ['pay_code', 'eq', $searchData['pay_code']];
        }

        if (isset($searchData['goods_method']) && $searchData['goods_method'] != '') {
            $where[] = ['goods_method', 'eq', $searchData['goods_method']];
        }
        if (isset($searchData['use_commission']) && $searchData['use_commission'] > 0) {
            $where[] = ['use_commission', 'gt', 0];
        }

        if (isset($searchData['use_re_comm']) && $searchData['use_re_comm'] > 0) {
            $where[] = ['use_re_comm', 'gt', 0];
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

        $search->select('member_id', '会员', 3)->dataUrl(url('/admin/member/selectPage'));
        $search->text('order_sn', '订单sn', 3)->maxlength(50);
        $search->select('first_leader', '上级', 3)->dataUrl(url('/admin/member/selectPage'));
        $search->select('leader_id', '团队领导', 3)->dataUrl(url('/admin/member/selectPage'));
        $search->select('pay_status', '支付状态', 3)->options(RechargeModel::$pay_status_types);
        $search->select('pay_code ', '支付方式', 3)->options(RechargeModel::$pay_codes);
        $search->select('goods_method', '套餐', 3)->options(RechargeModel::$get_goods_methds);
        $search->select('use_commission', '使用采蜜豆', 3)->options([0 => '否', 1 => '是']);
        $search->select('use_re_comm', '使用复投豆', 3)->options([0 => '否', 1 => '是']);
        $search->datetime('start ', '支付时间', 3)->placeholder('起始');
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
        $table->show('nickname', '会员');
        $table->show('first_leader_name', '上级');
        $table->fields('order_sn', '订单sn/支付流水号')->with(
            $table->show('order_sn', '订单sn'),
            $table->show('transaction_id', '支付流水号')->default('--')
        );

        $table->show('buy_num', '购买份数');
        $table->match('goods_method', '套餐')->options(RechargeModel::$get_goods_methds);
        $table->show('account', '支付金额');
        $table->match('pay_code', '支付方式')->options(RechargeModel::$pay_codes);
        $table->fields('pay_status', '支付状态')->with(
            $table->match('pay_status', '支付状态')->options(RechargeModel::$pay_status_types)->mapClassWhenGroup([[1, 'success'], [2, 'danger']]),
            $table->show('pay_time', '支付时间')
        );

        $table->show('remark', '备注');
        $table->show('create_time', '创建时间')->getWrapper()->addStyle('width:150px');

        $table->getToolbar()
            ->btnAdd()
            ->btnRefresh();

        $table->getActionbar()
            ->btnPostRowid('pay', url('pay'), '支付', 'btn-success', '', '', '确定要设置为已支付吗？此操作和用户实际支付等效，成功后将按流程给上级代理分佣(若有上级)')
            ->btnPostRowid('close', url('close'), '关闭', 'btn-danger', '', '')
            ->btnView()
            ->mapClass([
                'pay' => ['hidden' => '__h_pay__'],
                'close' => ['hidden' => '__h_close__'],
            ]);

        foreach ($data as &$d) {
            $d['__h_pay__'] = $d['pay_status'] != RechargeModel::PAY_STATUS_0;
            $d['__h_close__'] = $d['pay_status'] != RechargeModel::PAY_STATUS_0;
        }

        $table->useCheckbox(false);
    }

    /**
     * Undocumented function
     * @title 开、关充值
     * @return mixed
     */
    public function open()
    {
        $config_key = 'commission';
        $theConfig = WebConfig::where(['key' => $config_key])->find();

        if ($theConfig) {
            $config = json_decode($theConfig['config'], 1);
            $config['recharge_open'] = input('isopen') == '1' ? 1 : 0;
            $res = WebConfig::where(['key' => $config_key])->update(['config' => json_encode($config)]);
            if ($res) {
                WebConfig::clearCache($config_key);
                return json([
                    'code' => 1,
                    'msg' => '操作成功',
                ]);
            }
        }

        return json([
            'code' => 0,
            'msg' => '操作失败',
        ]);
    }

    /**
     * Undocumented function
     * @title 支付
     *
     * @return void
     */
    public function pay()
    {
        $ids = input('ids');
        $logic = new PaymentLogic;
        $res = $logic->rechargePaySuccess($ids, ['pay_code' => 'other', 'admin_id' => session('admin_id') ?: 0]);

        if (!$res) {
            $this->success('操作失败');
        } else {
            $this->success('支付成功');
        }
    }

    /**
     * Undocumented function
     * @title 关闭
     *
     * @return void
     */
    public function close()
    {
        $ids = array_filter(explode(',', input('ids')));
        $res = $this->dataModel->where('id', 'in', $ids)->where('pay_status', 0)->update(['pay_status' => 2]);

        if (!$res) {
            $this->success('操作失败');
        } else {
            $this->success('操作成功');
        }
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

        if ($isEdit == 2) { //查看
            $form->show('nickname', '会员');
            $form->show('first_leader_name', '上级');
            $form->show('order_sn', 'sn编号');
            $form->show('transaction_id', '支付流水号')->default('--');
            $form->show('buy_num', '购买份数');
            $form->match('goods_method', '套餐')->options(RechargeModel::$get_goods_methds);
            $form->show('account', '支付金额');
            $form->match('pay_status', '支付状态')->options(RechargeModel::$pay_status_types);
            $form->match('pay_code', '支付方式')->options(RechargeModel::$pay_codes);
            $form->show('pay_time', '支付时间');
            $form->show('create_time', '创建时间');
            $form->show('admin_id', '操作人编号');
            $form->show('remark', '备注');

        } else if ($isEdit == 0) { //添加
            if ($member_id = input('member_id/d')) { //如果是从用户列表点某个用户进来的，直接操作这个用户，不用下拉选择了
                $member = Member::get($member_id);

                if (!$member) {
                    return $this->builder()->layer()->close(0, '用户不存在！id-' . $member_id);
                }
                $form->show('nickname', '会员')->value($member['id'] . '#' . $member['nickname']);

                $form->hidden('member_id')->value($member_id);
            } else {
                $form->select('member_id', '会员')->dataUrl(url('/admin/member/selectPage'))->size(2, 4)->required();
            }
            $form->select('goods_method', '套餐')->required()->options(RechargeModel::$get_goods_methds);
            $form->textarea('remark', '备注')->maxlength(200);
        } else //编辑
        {
        }
    }

    /**
     * 保存数据
     *
     * @param integer $id
     * @return void
     */
    private function save($id = 0)
    {
        $data = request()->only([
            'member_id',
            'goods_method',
            'remark',
        ], 'post');

        $result = $this->validate($data, [
            'member_id|会员' => 'require',
            'goods_method|方式' => 'require|float|egt:0',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        $logic = new RechargeLogic;

        if ($id) {
            $this->error('不允许的操作');
        } else {
            $res = $logic->create($data['member_id'], $data);

            if ($res['code'] != 1) {
                $this->error('操作失败，' . $res['msg']);
            }
        }

        return $this->builder()->layer()->closeRefresh(1, '操作成功');
    }
}
