<?php

namespace app\admin\controller;

use app\common\model\CmsCategory;
use app\common\model\CmsContent as ContentModel;
use app\common\model\CmsTag;
use think\Controller;
use tpext\builder\traits\HasBuilder;
use tpext\myadmin\admin\model\AdminUser;

/**
 * Undocumented class
 * @title 内容管理
 */
class Cmscontent extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var ContentModel
     */
    protected $dataModel;
    /**
     * Undocumented variable
     *
     * @var CmsCategory
     */
    protected $categoryModel;

    protected function initialize()
    {
        $this->dataModel = new ContentModel;
        $this->categoryModel = new CmsCategory;
        $this->pageTitle = '内容管理';
        $this->enableField = 'is_show';
        $this->pagesize = 6;

        $this->indexWith = ['category'];

        //左侧树
        $this->treeModel = $this->categoryModel;//分类模型
        $this->treeTextField = 'name';//分类模型中的分类名称字段
        $this->treeKey = 'category_id';//关联的键　localKey
        
        $this->indexFieldsExcept = 'content';//排除某字段
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['title'])) {
            $where[] = ['title', 'like', '%' . $searchData['title'] . '%'];
        }

        if (!empty($searchData['author'])) {
            $where[] = ['author', 'like', '%' . $searchData['author'] . '%'];
        }

        if (!empty($searchData['category_id'])) {
            $where[] = ['category_id', '=', $searchData['category_id']];
        }

        if (isset($searchData['is_show']) && $searchData['is_show'] != '') {
            $where[] = ['is_show', '=', $searchData['is_show']];
        }

        if (!empty($searchData['tags'])) {
            $where[] = ['tags', 'like', '%' . $searchData['tags'] . '%'];
        }

        if (isset($searchData['attr'])) {
            if (in_array('is_recommend', $searchData['attr'])) {
                $where[] = ['is_recommend', '=', 1];
            }
            if (in_array('is_hot', $searchData['attr'])) {
                $where[] = ['is_hot', '=', 1];
            }
            if (in_array('is_top', $searchData['attr'])) {
                $where[] = ['is_top', '=', 1];
            }
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
        $search->text('author', '作者', 3)->maxlength(20);
        $search->select('category_id', '栏目', 3)->dataUrl(url('/admin/cmscategory/selectPage'));
        $search->select('is_show', '显示', 3)->options([1 => '是', 0 => '否']);
        $search->select('tags', '标签', 3)->dataUrl(url('/admin/cmstag/selectPage'));
        $search->checkbox('attr', '属性', 3)->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶']);
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
        $table->image('logo', '封面图')->default('/static/images/logo.png')->thumbSize(60, 60);
        $table->text('title', '标题')->autoPost()->getWrapper()->addStyle('max-width:200px');
        $table->show('category.name', '栏目');
        $table->file('video', '视频')->thumbSize(60, 60)->jsOptions(['fileSingleSizeLimit' => 50 * 1024 * 1024]);
        $table->show('author', '作者')->default('暂无');
        $table->show('source', '来源')->default('暂无');
        $table->matches('tags', '标签')->optionsData(CmsTag::field('id,name')->select());
        $table->switchBtn('is_show', '显示')->default(1)->autoPost();
        $table->checkbox('attr', '属性')->autoPost(url('editAttr'))->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶'])->inline(false);
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->text('click', '点击量')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->show('publish_time', '发布时间')->getWrapper()->addStyle('width:160px');
        $table->raw('times', '添加/修改时间')->getWrapper()->addStyle('width:160px');

        foreach ($data as &$d) {
            $d['times'] = $d['create_time'] . '<br>' . $d['update_time'];
        }

        unset($d);

        $table->sortable('id,sort,click');

        $table->getToolbar()
            ->btnAdd('', '添加', 'btn-primary', 'mdi-plus', 'data-layer-size="98%,98%"')
            ->btnEnableAndDisable('显示', '隐藏')
            ->btnDelete()
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit('', '', 'btn-primary', 'mdi-lead-pencil', 'data-layer-size="98%,98%"')
            ->btnDelete();
    }

    /**
     * Undocumented function
     * @title 属性修改
     *
     * @return mixed
     */
    public function editAttr()
    {
        $id = input('post.id/d', '');
        $value = input('post.value', '');

        if (empty($id)) {
            $this->error('参数有误');
        }

        $attr = explode(',', $value);

        $data = [];

        if (!empty($attr)) {
            $data['is_recommend'] = in_array('is_recommend', $attr);
            $data['is_hot'] = in_array('is_hot', $attr);
            $data['is_top'] = in_array('is_top', $attr);
        } else {
            $data['is_recommend'] = 0;
            $data['is_hot'] = 0;
            $data['is_top'] = 0;
        }

        $res = $this->dataModel->update($data, [$this->getPk() => $id]);

        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无更改');
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

        $admin = !$isEdit ? AdminUser::current() : null;

        $form->defaultDisplayerSize(12, 12);

        $form->left(7)->with(function () use ($form) {
            $form->text('title', '标题')->required()->maxlength(55);
            $form->select('category_id', '栏目')->required()->dataUrl(url('/admin/cmscategory/selectPage'));
            $form->multipleSelect('tags', '标签')->dataUrl(url('/admin/cmstag/selectPage'))->help('可到【标签管理】菜单添加标签');
            $form->tags('keyword', '关键字');
            $form->textarea('description', '摘要')->maxlength(255);

            $form->editor('content', '内容')->required();
        });

        $form->right(5)->with(function () use ($form, $admin, $isEdit) {
            $form->image('logo', '封面图')->mediumSize();
            $form->file('video', '视频')->video()->mediumSize();
            $form->file('attachment', '附件')->mediumSize();
            $form->text('author', '作者', 6)->maxlength(33)->default($admin ? $admin['name'] : '');
            $form->text('source', '来源', 6)->maxlength(55)->default($admin && $admin['group'] ? $admin['group']['name'] : '');
            $form->datetime('publish_time', '发布时间')->required()->default(date('Y-m-d H:i:s'));
            $form->number('click', '点击量', 6)->default(0);
            $form->number('sort', '排序', 6)->default(0);

            $form->checkbox('attr', '属性')->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶']);
            $form->switchBtn('is_show', '显示')->default(1);

            if ($isEdit) {
                $form->show('create_time', '添加时间', 6);
                $form->show('update_time', '修改时间', 6);
            }
        });

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
            'category_id',
            'tags',
            'keyword',
            'description',
            'logo',
            'video',
            'attachment',
            'author',
            'source',
            'publish_time',
            'sort',
            'is_show',
            'content',
            'attr',
            'click',
        ], 'post');

        $result = $this->validate($data, [
            'title|标题' => 'require',
            'category_id|栏目' => 'require',
            'content|内容' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if (isset($data['attr']) && !empty($data['attr'])) {
            $data['is_recommend'] = in_array('is_recommend', $data['attr']);
            $data['is_hot'] = in_array('is_hot', $data['attr']);
            $data['is_top'] = in_array('is_top', $data['attr']);
        } else {
            $data['is_recommend'] = 0;
            $data['is_hot'] = 0;
            $data['is_top'] = 0;
        }

        if (isset($data['tags']) && !empty($data['tags'])) {
            $data['tags'] = ',' . implode(',', $data['tags']) . ',';
        } else {
            $data['tags'] = '';
        }

        if ($data['category_id']) {
            $parent = $this->categoryModel->find($data['category_id']);
            if ($parent && $parent['type'] == 2) {
                $this->error($parent['name'] . '是目录，不允许存放文章，请重新选择');
            }
        }

        if (!$id) {
            $data['create_user'] = session('admin_id');
        }

        return $this->doSave($data, $id);
    }
}
