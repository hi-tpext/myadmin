<?php

namespace app\admin\controller;

use app\common\logic\GoodsLogic;
use app\common\model\ShopBrand;
use app\common\model\ShopCategory;
use app\common\model\ShopGoods as GoodstModel;
use app\common\model\ShopGoodsAttr;
use app\common\model\ShopGoodsSpec;
use app\common\model\ShopGoodsSpecPrice;
use app\common\model\ShopGoodsSpecValue;
use app\common\model\ShopTag;
use think\Controller;
use think\facade\Db;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 产品管理
 */
class Shopgoods extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var GoodstModel
     */
    protected $dataModel;

    /**
     * Undocumented variable
     *
     * @var ShopCategory
     */
    protected $categoryModel;

    /**
     * Undocumented variable
     *
     * @var ShopBrand
     */
    protected $brandModel;

    /**
     * Undocumented variable
     *
     * @var ShopGoodsSpecValue
     */
    protected $specValueModel;

    /**
     * Undocumented variable
     *
     * @var ShopGoodsSpecPrice
     */
    protected $specPriceModel;

    protected function initialize()
    {
        $this->dataModel = new GoodstModel;
        $this->categoryModel = new ShopCategory;
        $this->brandModel = new ShopBrand;
        $this->specValueModel = new ShopGoodsSpecValue;
        $this->specPriceModel = new ShopGoodsSpecPrice;

        $this->pageTitle = '产品管理';
        $this->enableField = 'on_sale';
        $this->pagesize = 8;

        $this->selectSearch = 'name|spu';
        $this->selectFields = 'id,name,spu'; //优化查询
        $this->selectTextField = '{name}#{spu}';

        $this->indexWith = ['category'];
    }

    /**
     * Undocumented function
     *
     * @title 下拉选择产品规格
     * @return mixed
     */
    public function specList()
    {
        $selected = input('selected');

        if ($selected) {
            return json(
                [
                    'data' => [],
                ]
            );
        }
        $q = input('q');
        $logic = new GoodsLogic;
        $specList = $logic->getSpecList($q);
        return json(
            [
                'data' => $specList,
                'has_more' => 0,
            ]
        );
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['kwd'])) {
            $where[] = ['name|spu', 'like', '%' . $searchData['kwd'] . '%'];
        }

        if (!empty($searchData['category_id'])) {
            $where[] = ['category_id', '=', $searchData['category_id']];
        }

        if (!empty($searchData['brand_id'])) {
            $where[] = ['brand_id', '=', $searchData['brand_id']];
        }

        if (!empty($searchData['admin_group_id'])) {
            $where[] = ['admin_group_id', '=', $searchData['admin_group_id']];
        }

        if (isset($searchData['on_sale']) && $searchData['on_sale'] != '') {
            $where[] = ['on_sale', '=', $searchData['on_sale']];
        }

        if (isset($searchData['share_commission']) && $searchData['share_commission'] != '') {
            if ($searchData['share_commission'] == 1) {
                $where[] = ['share_commission', '>', 0];
            }
            if ($searchData['share_commission'] == 0) {
                $where[] = ['share_commission', '=', 0];
            }
        }

        if (isset($searchData['is_show']) && $searchData['is_show'] != '') {
            $where[] = ['is_show', '=', $searchData['is_show']];
        }

        if (!empty($searchData['tags'])) {
            $where[] = ['tags', 'like', '%,' . $searchData['tags'] . ',%'];
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

        $search->text('kwd', '名称/spu', 3)->maxlength(20);
        $search->select('category_id', '分类', 3)->dataUrl(url('/admin/shopcategory/selectPage'));
        $search->select('brand_id', '品牌', 3)->dataUrl(url('/admin/shopbrand/selectPage'));
        $search->select('is_show', '显示', 3)->options([1 => '是', 0 => '否']);
        $search->select('admin_group_id', '商家', 3)->dataUrl(url('/admin/group/selectPage'));
        $search->select('on_sale', '上架', 3)->options([1 => '是', 0 => '否']);
        $search->select('share_commission', '分销', 3)->options([1 => '是', 0 => '否']);
        $search->select('tags', '标签', 3)->dataUrl(url('/admin/shoptag/selectPage'));
        $search->multipleSelect('attr', '属性', 3)->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶']);
    }

    public function index()
    {
        $builder = $this->builder($this->pageTitle, $this->indexText);

        $tree = $builder->tree('1 left-tree');

        $tree->fill($this->categoryModel->select());

        $tree->trigger('.row-category_id');

        $this->table = $builder->table('1 right-list');

        $builder->addStyleSheet('
            .left-tree
            {
                width:12%;
                float:left;
            }

            .right-list
            {
                width:88%;
                float:right;
            }
        ');

        $this->table->pk($this->getPk());
        $this->search = $this->table->getSearch();

        $this->buildSearch();
        $this->buildDataList();

        if (request()->isAjax()) {
            return $this->table->partial()->render();
        }

        return $builder->render();
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
        $table->image('logo', '封面图')->thumbSize(60, 60);
        $table->fields('name_spu', '名称/spu')->with(
            $table->show('name', '名称'),
            $table->show('spu', 'spu')
        )->getWrapper()->addStyle('max-width:200px');
        $table->show('category.name', '分类');
        $table->show('admin_group', '商家');
        $table->show('sale_price', '售价');
        $table->show('share_commission', '分销佣金');
        $table->matches('tags', '标签')->optionsData(ShopTag::field('id,name')->select(), 'name');
        $table->checkbox('attr', '属性')->autoPost(url('editAttr'))->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶'])->inline(false);
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->text('click', '点击量')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->text('stock', '库存')->autoPost('', true)->mapClass(['1'], 'disabled', 'many_price')->getWrapper()->addStyle('width:80px');
        $table->text('sales_sum', '销量')->autoPost('', true)->getWrapper()->addStyle('width:80px');
        $table->switchBtn('on_sale', '上架')->autoPost('', true);
        $table->switchBtn('is_show', '显示')->autoPost('', true);
        $table->show('publish_time', '上架时间')->getWrapper()->addStyle('width:160px');

        $table->fields('times', '添加/修改时间')->with(
            $table->show('create_time', '添加时间'),
            $table->show('update_time', '修改时间')
        )->getWrapper()->addStyle('width:160px');

        foreach ($data as &$d) {
            $d['__h_price__'] = !$d['many_price'];
        }

        unset($d);

        $table->sortable('id,sort,click,stock,on_sale,is_show,sale_price');

        $table->getToolbar()
            ->btnAdd('', '添加', 'btn-primary', 'mdi-plus', 'data-layer-size="98%,98%"')
            ->btnEnableAndDisable('上架', '下架')
            ->btnDelete()
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit('', '', 'btn-primary', 'mdi-lead-pencil', 'data-layer-size="98%,98%"')
            ->btnLink('price', url('edit', ['id' => '__data.pk__', 'tab' => 4]), '库存', 'btn-info', 'mdi mdi-coin', 'title="库存价格管理" data-layer-size="98%,98%"')
            ->btnDelete()
            ->mapClass([
                'price' => ['hidden' => '__h_price__'],
            ]);
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

        $specList = $isEdit ? ShopGoodsSpec::where(['goods_id' => $data['id']])->order('sort')->select() : [];
        $attrList = $isEdit ? ShopGoodsAttr::where(['goods_id' => $data['id']])->order('sort')->select() : [];
        $priceList = $isEdit ? $this->specPriceModel->where(['goods_id' => $data['id']])->select() : [];

        $tab = input('tab', 0);

        $form->tab('基本信息');

        /**
         * 
         * fields,items 的 with 方法有三种方式：
         * 
         * 1.with参数是可变参数的多个fields:
         * $from->fields('demo1')->with(
         *    $form->field('f1', 'f1'),
         *    $form->field('f2', 'f2'),
         *    //....
         *    $form->field('fn', 'fn'), //最后一个跟随着`,`号，在php低版本会报错，删除后正常
         * );
         * 
         * 2.with参数是一个数组包含多个fields:
         * $from->fields('demo2')->with([
         *    $form->field('f1', 'f1'),
         *    $form->field('f2', 'f3'),
         *    //....
         *    $form->field('fn', 'fn'), //因为在数组中，最后一个跟随着`,`号也不报错，兼容性更好（一般作为方法1的改进，在php低版本中的折衷方案）
         * ]
         * );
         * 
         * 3.with参数是一个用匿名方法:
         * $from->fields('demo1')->with(
         *     function ($f) use ($form) {
         *         $form->field('f1', 'f1');
         *         $form->field('f2', 'f3');
         *         //....
         *         $form->field('fn', 'fn');//格式更自由，代码格式化以后层次结构好
         *     }
         * );
         */

         //
        $form->fields('', '', 7)->size(0, 12)->showLabel(false);

        $form->defaultDisplayerSize(12, 12);
        $form->text('name', '名称')->required()->maxlength(55);
        $form->select('category_id', '分类')->required()->dataUrl(url('/admin/shopcategory/selectPage'));
        $form->select('brand_id', '品牌')->dataUrl(url('/admin/shopbrand/selectPage'));
        $form->select('admin_group_id', '商家')->dataUrl(url('/admin/group/selectPage'));
        $form->text('spu', 'spu码')->maxlength(100);
        $form->multipleSelect('tags', '标签')->dataUrl(url('/admin/shoptag/selectPage'))->help('可到【标签管理】菜单添加标签');
        $form->tags('keyword', '关键字');
        $form->textarea('description', '摘要')->maxlength(255);
        $form->wangEditor('content', '产品详情')->required();

        $form->fieldsEnd();

        $hasManyPrice = $isEdit && count($priceList) > 0 ? true : false;

        //  以下代码展示使用匿名方法的with
        $form->fields('', '', 5)->size(0, 12)->showLabel(false)->with(
            function ($f) use ($form, $isEdit, $hasManyPrice) { //匿名方法中使用了外部变量，要使用 use($vars...)
                //$form->image('logo', '封面图')->required()->mediumSize();
                $f->image('logo', '封面图')->required()->mediumSize(); //$f 和 use中的 $from都一样，$from的好处是可以有ide的代码提示

                $form->text('share_commission', '分销佣金')->default(0);

                $form->text('sale_price', '销售价', 4)->required()->readonly($hasManyPrice)->help($hasManyPrice ? '存在多个规格型号，显示价为所有规格型号最低价' : '');
                $form->text('market_price', '市场价', 4);
                $form->text('cost_price', '成本价', 4);
                $form->html('');
                $form->number('stock', '库存', 4)->default(99)->readonly($hasManyPrice)->help($hasManyPrice ? '存在多个规格型号，总库存为所有规格型号总和' : '');
                $form->number('click', '点击量', 4)->default(1);
                $form->number('sales_sum', '销量', 4)->default(0);
                $form->html('');
                $form->radio('on_sale', '上架', 6)->blockStyle()->options([1 => '已上架', 0 => '未上架'])->default(0)->help('下架后不显示也不可购买');
                $form->datetime('publish_time', '上架时间', 6)->required()->default(date('Y-m-d H:i:s'));
                $form->checkbox('attr', '属性')->blockStyle()->checkallBtn()->options(['is_recommend' => '推荐', 'is_hot' => '热门', 'is_top' => '置顶']);
                $form->switchBtn('is_show', '显示')->default(1)
                    ->help('仅对已上架产品生效，未上架无论如何都不显示<br>已上架产品隐藏后不在产品列表显示，但仍可购买(通过某些途径进入产品详情页)');
                $form->number('sort', '排序')->default(0);
                $form->number('weight', '重量')->default(1000)->help('单位:克');

                if ($isEdit) {
                    $form->show('create_time', '添加时间', 6);
                    $form->show('update_time', '修改时间', 6);
                }
            }
        );

        $form->tab('多图/视频');
        $form->fields('', '', 10)->size(0, 12)->showLabel(false);
        $form->multipleImage('images', '多图')->video()->mediumSize();
        $form->file('video', '视频')->video()->mediumSize()->jsOptions(['fileSingleSizeLimit' => 50 * 1024 * 1024]);
        $form->fieldsEnd();

        $form->tab('规格型号/属性', $tab == 3);

        $form->items('spec_list', '规格型号')->dataWithId($specList)->with(
            $form->text('name', '名称')->placeholder('规格名称，如颜色')->maxlength(55)->required()->getWrapper()->addStyle('width:200px;'),
            $form->text('sort', '排序')->placeholder('规格名称，如颜色')->default(1)->required()->getWrapper()->addStyle('width:80px;'),
            $form->tags('value', '可选值')->required()->getWrapper()->addStyle('min-width:70%;')
        )->help('【规格型号】会影响价格，根据排列组合，用户选择不同价格会不同。多个值用英文`,`号或回车键<i class="mdi mdi-subdirectory-arrow-left"></i>分割');

        $form->items('attr_list', '产品属性')->dataWithId($attrList)->with(
            $form->text('name', '名称')->placeholder('属性名称，如生产日期')->maxlength(55)->required()->getWrapper()->addStyle('width:200px;'),
            $form->text('sort', '排序')->placeholder('规格名称，如颜色')->default(1)->required()->getWrapper()->addStyle('width:80px;'),
            $form->text('value', '属性值')->required()->getWrapper()->addStyle('min-width:70%;')
        )->help('【属性】不影响价格，仅展示');

        if ($isEdit && count($specList)) {
            $form->tab('库存/价格设置', $tab == 4);
            $form->items('price_list', '设置')->help('规格型号增加/删除/修改后，先保存一次此处才显示最新规格');
            foreach ($specList as $spec) {

                foreach ($priceList as &$price) {
                    if ($price['data']) {
                        $savedData = json_decode($price['data'], 1);
                        if ($savedData && isset($savedData[$spec['id']])) {
                            $price['spec_id' . $spec['id']] = $savedData[$spec['id']];
                        }
                    }
                }

                $valueList = $this->specValueModel->where(['spec_id' => $spec['id']])->select();
                $form->select('spec_id' . $spec['id'], $spec['name'])->optionsData($valueList, 'value')->required();
            }

            $data['price_list'] = $priceList;

            $form->text('sku', 'SKU编码');
            $form->text('sale_price', '销售价格')->required();
            $form->number('stock', '库存')->required();

            $form->itemsEnd();
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
        $isEdit = $id > 0;
        $data = request()->only([
            'name',
            'category_id',
            'brand_id',
            'admin_group_id',
            'tags',
            'keyword',
            'description',
            'spu',
            'logo',
            'video',
            'images',
            'on_sale',
            'sale_price',
            'market_price',
            'cost_price',
            'share_commission',
            'stock',
            'publish_time',
            'sort',
            'is_show',
            'content',
            'attr',
            'click',
            'weight',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'category_id|栏目' => 'require',
            'content|内容' => 'require',
            'stock|库存' => 'require|number',
            'sale_price|销售价' => 'require|float',
            'share_commission' => 'float',
            'admin_group_id|商家' => 'require|number|>:0',
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

        $data['market_price'] = $data['market_price'] ? $data['market_price'] : $data['sale_price'];
        $data['cost_price'] = $data['cost_price'] ? $data['cost_price'] : $data['sale_price'];

        if ($data['category_id']) {
            $parent = $this->categoryModel->find($data['category_id']);
            if ($parent && $parent['type'] == 2) {
                $this->error('分类[' . $parent['name'] . ']是目录，不允许存放产品，请重新选择');
            }
        }

        if ($data['brand_id']) {
            $parent = $this->brandModel->find($data['brand_id']);
            if ($parent && $parent['type'] == 2) {
                $this->error('品牌[' . $parent['name'] . ']是目录，不允许存放产品，请重新选择');
            }
        }
        $res = 0;

        if ($id) {
            $exists = $this->dataModel->where(['id' => $id])->find();
            if ($exists) {
                $res = $exists->force()->save($data);
            }
        } else {
            $data['create_user'] = session('admin_id');
            $res = $this->dataModel->exists(false)->save($data);
            if ($res) {
                $id = $this->dataModel->id;
            }
        }

        if (!$res) {
            $this->error('保存产品信息失败');
        }

        $changes = 0;

        $logic = new GoodsLogic;

        Db::startTrans();

        $errors1 = $logic->savelsit($id, 'spec_list', $changes);
        $errors2 = $logic->savelsit($id, 'attr_list');
        $errors3 = $isEdit ? $logic->savePrices($id) : [];

        Db::commit();

        $errors = array_merge($errors1, $errors2, $errors3);

        $specCount = ShopGoodsSpec::where(['goods_id' => $id])->count();
        $priceCount = $this->specPriceModel->where(['goods_id' => $id])->count();

        if ($priceCount) { //多规格情况，价格和总库存处理
            $minPrice = $this->specPriceModel->where(['goods_id' => $id])->min('sale_price');
            $allStock = $this->specPriceModel->where(['goods_id' => $id])->sum('stock');

            $this->dataModel->where(['id' => $id])->update(['sale_price' => $minPrice, 'stock' => $allStock]);
        }

        if (empty($errors)) {

            $script = '<script>parent.$(".search-refresh").trigger("click");</script>'; //刷新列表页面

            if ($specCount && !$priceCount) {
                return $this->success('保存成功，请设置价格、库存', url('edit', ['id' => $id, 'tab' => 4]), ['script' => $script], 1);
            }

            if ($changes > 0) {
                return $this->success('保存成功，规格型号已修改，请设置价格、库存', url('edit', ['id' => $id, 'tab' => 4]), ['script' => $script], 1);
            }

            return $this->builder()->layer()->closeRefresh(1, '保存成功');
        }

        $tab = 0;
        if (!empty($errors1) || !empty($errors2)) {
            $tab = 3;
        } else if (!empty($errors3)) {
            $tab = 4;
        }

        $this->error(implode('<br>', $errors), url('edit', ['id' => $id, 'tab' => $tab]), '', 3);
    }
}
