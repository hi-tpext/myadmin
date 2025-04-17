<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\builder\traits\actions;
use tpext\cms\common\taglib\Processer;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsTag as TagModel;

/**
 * Undocumented class
 * @title 合集管理
 */
class Cmstag extends Controller
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
     * @var TagModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new TagModel;

        $this->pageTitle = '合集管理';
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
        $table->raw('name', '名称')->to('<a href="{preview_url}" target="_blank">{val}</a>');
        $table->show('description', '描述');
        $table->switchBtn('is_show', '显示')->default(1)->autoPost();
        $table->text('sort', '排序')->autoPost('', true);

        $table->sortable('id,sort');

        $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnDelete();

        $template = CmsTemplate::where('id', 1)->find();
        Processer::setPath($template['prefix']);

        foreach ($data as &$d) {
            $d['preview_url'] = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $template['prefix']) . Processer::resolveTagPath($d) . '.html';
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

        $form->text('name', '名称')->required();
        $form->image('logo', '封面图片');
        $form->textarea('description', '描述')->maxlength(255);
        $form->text('link', '链接');
        $form->switchBtn('is_show', '显示')->default(1);
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
