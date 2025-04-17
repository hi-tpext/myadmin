<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\builder\traits\actions;
use tpext\cms\common\model\CmsBanner as BannerModel;
use tpext\cms\common\model\CmsPosition as CmsPositionModel;

/**
 * Undocumented class
 * @title 广告管理
 */
class Cmsbanner extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;
    use actions\HasEdit;
    use actions\HasView;
    use actions\HasDelete;
    use actions\HasEnable;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var BannerModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new BannerModel;
        $this->pageTitle = '广告管理';
        $this->enableField = 'is_show';
        $this->pagesize = 6;

        $this->selectSearch = 'title';
        $this->selectFields = 'id,title';
        $this->selectTextField = 'title';

        $this->indexWith = ['position'];

        $this->treeModel = new CmsPositionModel; //分类模型
        $this->treeTextField = 'name'; //分类模型中的分类名称字段
        $this->treeKey = 'position_id'; //关联的键　localKey
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['title'])) {
            $where[] = ['title', 'like', '%' . $searchData['title'] . '%'];
        }

        if (!empty($searchData['position_id'])) {
            $where[] = ['position_id', '=', $searchData['position_id']];
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
        $search->select('position_id', '位置', 3)->dataUrl(url('/admin/cmsposition/selectpage'));
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
            ->btnView()
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
        $form->select('position_id', '位置')->required()->dataUrl(url('/admin/cmsposition/selectpage'));
        $form->textarea('description', '摘要')->maxlength(255);
        $form->text('link', '链接')->maxlength(255)->default('#');
        $form->image('image', '图片')->mediumSize();
        $form->number('sort', '排序')->default(0);
        $form->switchBtn('is_show', '显示')->default(1);
        if ($isEdit) {
            $form->hidden('id');
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
    protected function save($id = 0)
    {
        $data = request()->only([
            'id',
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
            'position_id|位置' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
