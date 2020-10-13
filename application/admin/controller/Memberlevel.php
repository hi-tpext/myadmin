<?php

namespace app\admin\controller;

use think\Controller;
use tpext\builder\traits\HasBuilder;
use app\common\model\MemberLevel as LevelModel;

/**
 * Undocumented class
 * @title 会员等级
 */
class Memberlevel extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var LevelModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new LevelModel;
        $this->pageTitle = '会员等级';
        $this->sortOrder = 'level asc';
        $this->selectSearch = 'name';
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];

        if (!empty($searchData['name'])) {
            $where[] = ['name', 'like', '%' . $searchData['name'] . '%'];
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
        $search->text('name', '名称', 3)->maxlength(20);
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
        $table->text('name', '名称')->autoPost();
        $table->text('level', '等级')->autoPost('', true)->getWrapper()->addStyle('width:150px');
        $table->show('points', '积分条件');
        $table->show('spend_money', '累计消费')->default(0);
        $table->show('member_count', '人数统计')->default(0);
        $table->show('description', '描述');
        $table->show('create_time', '添加时间')->getWrapper()->addStyle('width:150px');
        $table->show('update_time', '更新时间')->getWrapper()->addStyle('width:150px');
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

        $form->text('name', '名称');
        $form->number('level', '等级');
        $form->text('points', '积分条件')->size(2, 3);
        $form->text('spend_money', '累计消费')->size(2, 3);
        $form->textarea('description', '描述');

        if ($isEdit) {
            $form->show('member_count', '人数统计');
            $form->show('create_time', '添加时间');
            $form->show('update_time', '更新时间');
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
            'name',
            'level',
            'spend_money',
            'points',
            'description',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'level|等级' => 'require|number',
            'spend_money|累计消费' => 'require|float',
            'points|积分条件' => 'require|float',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
