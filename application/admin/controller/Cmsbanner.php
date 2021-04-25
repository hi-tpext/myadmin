<?php

namespace app\admin\controller;

use app\common\model\CmsBanner as BannerModel;
use app\common\model\CmsPosition;
use think\Controller;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 广告管理
 */
class Cmsbanner extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var BannerModel
     */
    protected $dataModel;
    /**
     * Undocumented variable
     *
     * @var CmsPosition
     */
    protected $positionModel;

    protected function initialize()
    {
        $this->dataModel = new BannerModel;
        $this->positionModel = new CmsPosition;
        $this->pageTitle = '广告管理';
        $this->enableField = 'is_show';
        $this->pagesize = 6;

        $this->selectSearch = 'title';
        $this->selectFields = 'id,title';
        $this->selectTextField = 'title';

        $this->indexWith = 'position';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['title'])) {
            $where[] = ['title', 'like', '%' . $searchData['title'] . '%'];
        }

        if (!empty($searchData['position_id'])) {
            $where[] = ['position_id', 'eq', $searchData['position_id']];
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

        $search->text('title', '标题', 3)->maxlength(20);
        $search->select('position_id', '位置', 3)->optionsData($this->positionModel->all());
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
        $table->image('image', '图片')->thumbSize(70, 70);
        $table->text('title', '标题')->autoPost()->getWrapper()->addStyle('max-width:200px');
        $table->show('position.name', '位置');
        $table->show('description', '摘要')->default('暂无')->getWrapper()->addStyle('max-width:200px');
        $table->show('link', '链接');
        $table->text('sort', '排序')->autoPost()->getWrapper()->addStyle('max-width:100px');
        $table->switchBtn('is_show', '显示')->default(1)->autoPost();
        $table->show('create_time', '添加时间')->getWrapper()->addStyle('width:180px');
        $table->show('update_time', '修改时间')->getWrapper()->addStyle('width:180px');

        $table->sortable('id,sort');

        $table->getToolbar()
            ->btnAdd()
            ->btnEnableAndDisable('显示', '隐藏')
            ->btnDelete()
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit()
            ->btnDelete();
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

        $form->text('title', '标题')->required()->maxlength(55);
        $form->select('position_id', '位置')->required()->optionsData($this->positionModel->all());
        $form->textarea('description', '摘要')->maxlength(255);
        $form->text('link', '链接')->maxlength(255)->default('#');
        $form->image('image', '图片')->mediumSize();
        $form->number('sort', '排序')->default(0);
        $form->switchBtn('is_show', '显示')->default(1);
        if ($isEdit) {
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
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
            'title',
            'position_id',
            'description',
            'link',
            'image',
            'sort',
            'is_show',
        ], 'post');

        $result = $this->validate($data, [
            'title|标题' => 'require',
            'image|图片' => 'require',
            'position_id|位置' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
