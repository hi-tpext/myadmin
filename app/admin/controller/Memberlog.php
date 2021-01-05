<?php

namespace app\admin\controller;

use app\common\model\MemberLog as LogModel;
use think\Controller;
use tpext\builder\traits\actions;

/**
 * Undocumented class
 * @title 会员日志
 */
class Memberlog extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;
    use actions\HasView;

    /**
     * Undocumented variable
     *
     * @var LogModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new LogModel;
        $this->pageTitle = '会员日志';

        $this->indexWith = ['member'];
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];
        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', '=', $searchData['member_id']];
        }

        if (!empty($searchData['desc'])) {
            $where[] = ['desc', 'like', '%' . $searchData['desc'] . '%'];
        }

        if (!empty($searchData['type'])) {
            $where[] = [$searchData['type'], 'neq', 0];
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
        $search->text('desc', '描述', 3)->maxlength(20);
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
        $table->show('member.nickname', '会员');
        $table->show('desc', '描述');
        $table->show('change', '等级变动');
        $table->show('create_time', '操作时间')->getWrapper()->addStyle('width:150px');

        $table->getToolbar()
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

            $form->show('id', 'ID');
            $form->show('nickname', '会员');
            $form->show('desc', '描述');
            $form->show('change', '等级变动');
            $form->show('create_time', '操作时间');
        }
    }
}
