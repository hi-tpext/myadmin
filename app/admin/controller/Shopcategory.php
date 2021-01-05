<?php

namespace app\admin\controller;

use think\Controller;
use tpext\builder\traits\actions\HasAutopost;
use tpext\builder\traits\actions\HasIAED;
use app\common\model\ShopCategory as Category;

/**
 * Undocumented class
 * @title 产品分类
 */
class Shopcategory extends Controller
{
    use HasIAED;
    use HasAutopost;

    /**
     * Undocumented variable
     *
     * @var Category
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new Category;

        $this->pageTitle = '产品分类';
        $this->sortOrder = 'id desc';
        $this->pagesize = 8;
    }

    /**
     * Undocumented function
     *
     * @title 下拉选择产品分类
     * @return mixed
     */
    public function selectPage()
    {

        $list = $this->dataModel->buildTree(0, 0, 0);
        $selected = input('selected');

        $data = [];

        foreach ($list as $k => $v) {
            if ($selected) {
                if ($selected && $k == $selected) {
                    $data[] = [
                        'id' => $k,
                        'name' => $v,
                    ];
                    break;
                }
            } else {
                $data[] = [
                    'id' => $k,
                    'name' => $v,
                ];
            }
        }

        return json(
            [
                'data' => $data,
                'has_more' => 0,
            ]
        );
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

        $tree = [0 => '根分类'];

        $tree += $this->dataModel->getOptionsData($isEdit ? $data['id'] : 0); //数组合并不要用 array_merge , 会重排数组键 ，作为options导致bug

        $form->text('name', '名称')->required();
        $form->select('parent_id', '上级')->required()->options($tree);
        $form->text('link', '链接');
        $form->image('logo', '封面图片');
        $form->switchBtn('is_show', '显示')->default(1);
        $form->radio('type', '类型')->default(1)->options([1 => '不限', 2 => '目录', 3 => '分类'])->required()->help('目录有下级，不能存产品。分类无下级，只能存产品');
        $form->text('sort', '排序')->default(0)->required();

        if ($isEdit) {
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }
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
        $table->raw('__text__', '结构')->getWrapper()->addStyle('text-align:left;');
        $table->image('logo', '封面')->thumbSize(50, 50);
        $table->show('link', '链接')->default('暂无');
        $table->text('name', '名称')->autoPost('', true)->getWrapper()->addStyle('max-width:80px');
        $table->switchBtn('is_show', '显示')->default(1)->autoPost()->getWrapper()->addStyle('width:80px');
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->show('goods_count', '产品统计')->getWrapper()->addStyle('width:80px');
        $table->raw('times', '添加/修改时间')->getWrapper()->addStyle('width:180px');

        foreach ($data as &$d) {
            $d['times'] = $d['create_time'] . '<br>' . $d['update_time'];
        }

        unset($d);

        $table->sortable([]);
    }

    private function save($id = 0)
    {
        $data = request()->only([
            'name',
            'parent_id',
            'logo',
            'link',
            'is_show',
            'type',
            'sort',
            'model',
            'attr',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'sort|排序' => 'require|number',
            'type|类型' => 'require|number',
            'parent_id|上级' => 'require|number',
            'is_show' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($data['parent_id']) {
            $parent = $this->dataModel->find($data['parent_id']);
            if ($parent && $parent['type'] == 3) {
                $this->error('[' . $parent['name'] . ']不允许有下级分类，请重新选择');
            }
        }

        if ($id && $data['parent_id'] == $id) {
            $this->error('上级不能是自己');
        }

        return $this->doSave($data, $id);
    }
}
