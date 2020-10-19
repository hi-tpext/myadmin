<?php

namespace app\admin\controller;

use app\common\logic\AccountLogic;
use app\common\model\Member;
use app\common\model\MemberAccount as AccountModel;
use think\Controller;
use tpext\builder\traits\actions;
use tpext\myadmin\admin\model\AdminUser;

/**
 * Undocumented class
 * @title 账户记录
 */
class Memberaccount extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;
    use actions\HasAdd;
    use actions\HasView;

    /**
     * Undocumented variable
     *
     * @var AccountModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new AccountModel;
        $this->pageTitle = '账户记录';
        $this->sortOrder = 'create_time desc';
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];
        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', 'eq', $searchData['member_id']];
        }

        if (!empty($searchData['type'])) {
            $where[] = [$searchData['type'], 'neq', 0];
        }

        if (!empty($searchData['remark'])) {
            $where[] = ['remark', 'like', '%' . $searchData['remark'] . '%'];
        }

        if (!empty($searchData['admin_id'])) {
            $where[] = ['admin_id', 'eq', $searchData['admin_id']];
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

        $search->select('member_id', '会员', 3)->dataUrl(url('/admin/member/selectPage'));
        $search->select('type', '变动类型', 3)->options(AccountModel::$types);
        $search->text('remark', '备注', 3)->maxlength(20);
        $search->select('admin_id', '管理员', 3)->optionsData(AdminUser::select(), 'name');
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
        $table->show('nickname', '昵称');
        $table->show('money', AccountModel::$types['money']);
        $table->show('points', AccountModel::$types['points']);
        $table->show('commission', AccountModel::$types['commission']);
        $table->show('remark', '备注');
        $table->match('admin_id', '管理员')->optionsData(AdminUser::select(), 'name');
        $table->show('create_time', '操作时间')->getWrapper()->addStyle('width:150px');

        $table->sortable('id,money,points,commission,shares,admin_id');

        $table->getToolbar()
            ->btnAdd()
            ->btnRefresh();

        $table->getActionbar()
            ->btnView();

        $table->useCheckbox(false);
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

            $form->show('money', AccountModel::$types['money']);
            $form->show('points', AccountModel::$types['points']);
            $form->show('commission', AccountModel::$types['commission']);

            $form->show('create_time', '操作时间');
            $form->show('admin_id', '操作人编号');
            $form->show('remark', '备注');
        } else if ($isEdit == 0) { //添加

            $types = AccountModel::$types;

            if ($member_id = input('member_id/d')) { //如果是从用户列表点某个用户进来的，直接操作这个用户，不用下拉选择了
                $member = Member::get($member_id);

                if (!$member) {
                    return $this->builder()->layer()->close(0, '用户不存在！id-' . $member_id);
                }

                $form->show('nickname', '会员')->value($member['id'] . '#' . $member['nickname']);

                $form->show('account', '账号情况')->value("{$types['money']}：{$member['money']},{$types['points']}：{$member['points']},{$types['commission']}：{$member['commission']}");

                $form->hidden('member_id')->value($member_id);
            } else {
                $form->select('member_id', '会员')->dataUrl(url('/admin/member/selectPage'))->size(2, 4)->required();
            }

            $form->fields('money', $types['money'])->with(
                $form->select('money_op', '操作类型', 4)->size(12, 12)->options([1 => '增加', 2 => '减少']),
                $form->text('money', '金额', 4)->size(12, 12)->default(0)
            );

            $form->fields('points', $types['points'])->with(
                $form->select('points_op', '操作类型', 4)->size(12, 12)->options([1 => '增加', 2 => '减少']),
                $form->text('points', '数量', 4)->size(12, 12)->default(0)
            );

            $form->fields('commission', $types['commission'])->with(
                $form->select('commission_op', '操作类型', 4)->size(12, 12)->options([1 => '增加', 2 => '减少']),
                $form->text('commission', '数量', 4)->size(12, 12)->default(0)
            );

            $form->textarea('remark', '备注')->maxlength(200)->required();
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
            'points',
            'money',
            'commission',
            'remark',
            'points_op',
            'money_op',
            'commission_op',
        ], 'post');

        $result = $this->validate($data, [
            'member_id|会员' => 'require',
            'points|'. AccountModel::$types['points'] => 'float|egt:0',
            'money|'. AccountModel::$types['money'] => 'float|egt:0',
            'commission|' . AccountModel::$types['commission'] => 'float|egt:0',
            'remark|备注' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if (empty($data['points']) && empty($data['money']) && empty($data['commission'])) {
            $this->error(AccountModel::getNames() . '不能全部为 0');
        }

        if ($data['money'] != 0) {
            if ($data['money_op'] == 1) {
            } else if ($data['money_op'] == 2) {
                $data['money'] *= -1;
            } else {
                $this->error('请选择[' . AccountModel::$types['money'] . ']操作类型，增加或减少');
            }
        }
        if ($data['points'] != 0) {
            if ($data['points_op'] == 1) {
                //
            } else if ($data['points_op'] == 2) {
                $data['points'] *= -1;
            } else {
                $this->error('请选择[' . AccountModel::$types['points'] . ']操作类型，增加或减少');
            }
        }
        if ($data['commission'] != 0) {
            if ($data['commission_op'] == 1) {
            } else if ($data['commission_op'] == 2) {
                $data['commission'] *= -1;
            } else {
                $this->error('请选择[' . AccountModel::$types['commission'] . ']操作类型，增加或减少');
            }
        }
        $logic = new AccountLogic;

        if ($id) {
            $this->error('不允许的操作');
        } else {
            $res = $logic->editMemberAccount($data['member_id'], $data);

            if ($res['code'] != 1) {
                $this->error('操作失败，' . $res['msg']);
            }
        }

        return $this->builder()->layer()->closeRefresh(1, '操作成功');
    }
}
