<?php

namespace app\admin\controller;

use app\common\model\CmsTag as TagModel;
use think\Controller;
use tpext\builder\traits\actions;

/**
 * Undocumented class
 * @title 内容标签
 */
class Cmstag extends Controller
{
    use actions\HasIAED;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var TagModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new TagModel;

        $this->pageTitle = '内容标签';
        $this->sortOrder = 'id desc';
        $this->pagesize = 8;

        $this->selectSearch = 'name';
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

        $form->text('name', '名称')->required();
        $form->image('logo', '封面图片');
        $form->textarea('description', '描述')->maxlength(255);
        $form->text('link', '链接');
        $form->switchBtn('is_show', '显示')->default(1);
        $form->number('sort', '排序')->default(0)->required();

        if ($isEdit) {
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }
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
        $table->image('logo', '封面')->thumbSize(50, 50);
        $table->text('name', '名称')->autoPost('', true);
        $table->show('description', '描述')->getWrapper()->addStyle('width:30%;');
        $table->switchBtn('is_show', '显示')->default(1)->autoPost()->getWrapper()->addStyle('width:120px');
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:120px');

        $table->sortable('id,sort');
    }

    private function save($id = 0)
    {
        $data = request()->only([
            'name',
            'logo',
            'description',
            'link',
            'is_show',
            'sort',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'sort|排序' => 'require|number',
            'is_show' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
