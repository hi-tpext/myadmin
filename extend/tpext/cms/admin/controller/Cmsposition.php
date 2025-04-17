<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\builder\traits\actions;
use tpext\cms\common\model\CmsBanner;
use tpext\cms\common\model\CmsPosition as Position;

/**
 * Undocumented class
 * @title 广告位置
 */
class Cmsposition extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;
    use actions\HasEdit;
    use actions\HasView;
    use actions\HasDelete;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var Position
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new Position;

        $this->pageTitle = '广告位置';
        $this->sortOrder = 'id desc';
        $this->pagesize = 8;

        $this->selectSearch = 'name';
        $this->selectFields = 'id,name';
        $this->selectTextField = 'name';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

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
        $table->switchBtn('is_show', '显示')->default(1)->autoPost()->getWrapper()->addStyle('width:120px');
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:120px');
        $table->show('banner_count', '内容统计')->getWrapper()->addStyle('width:80px');
        $table->show('start_time', '开始时间')->getWrapper()->addStyle('width:180px');
        $table->show('end_time', '结束时间')->getWrapper()->addStyle('width:180px');

        foreach ($data as &$d) {
            $d['__h_pv__'] = !$d['banner_count'];
        }

        unset($d);

        $table->sortable('id,sort');

        $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnLink('preview', url('preview', ['id' => '__data.pk__']), '预览', 'btn-info', 'mdi mdi-eye-outline', 'title="预览" data-layer-size="580px,460px"')
            ->btnDelete()
            ->mapClass([
                'preview' => ['hidden' => '__h_pv__'],
            ]);
    }

    /**
     * Undocumented function
     * @title 预览
     *
     * @return mixed
     */
    public function preview($id)
    {
        $list = CmsBanner::where('position_id', $id)->select();
        $builder = $this->builder('');
        $builder->swiper(6)->images($list);
        return $builder;
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
        $form->switchBtn('is_show', '显示')->default(1);
        $form->datetime('start_time', '开始时间')->required()->default(date('Y-m-d 00:00:00'));
        $form->datetime('end_time', '结束时间')->required()->default(date('Y-m-d 00:00:00', strtotime('+1year')));
        $form->number('sort', '排序')->default(0)->required();

        if ($isEdit) {
            $form->hidden('id');
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }
    }

    protected function save($id = 0)
    {
        $data = request()->only([
            'id',
            'name',
            'logo',
            'is_show',
            'start_time',
            'end_time',
            'sort',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'sort|排序' => 'require|number',
            'start_time|开始时间' => 'require|date',
            'end_time|结束时间' => 'require|date',
            'is_show' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
