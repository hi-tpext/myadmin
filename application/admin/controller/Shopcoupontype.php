<?php

namespace app\admin\controller;

use app\common\model\ShopCouponType as CouponTypeModel;
use app\common\model\ShopGoods;
use think\Controller;
use tpext\builder\traits\actions\HasAutopost;
use tpext\builder\traits\actions\HasIAED;

/**
 * Undocumented class
 * @title 优惠券类型
 */
class Shopcoupontype extends Controller
{
    use HasIAED;
    use HasAutopost;

    /**
     * Undocumented variable
     *
     * @var CouponTypeModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new CouponTypeModel;

        $this->pageTitle = '优惠券类型';
        $this->sortOrder = 'id desc';
        $this->pagesize = 14;

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

        $form->text('name', '名称')->autoPost('', true)->required();
        $form->radio('card_type', '类型')->options([1 => '折扣券', 2 => '购物券'])->readonly($isEdit)
            ->required()->help('折扣券：给产品打折；<br>购物券：购买一个面额以内产品任意，若是指定产品的，则不限面额');
        $form->radio('type', '发放方式')->options([0 => '面额模板', 1 => '按用户发放', 2 => '注册', 3 => '邀请', 4 => '线下发放'])->required();
        $form->text('money', '面额')->required()->default(0)->help('折扣（折扣券）或金额（购物券）');
        //$form->text('condition ', '使用条件');
        $form->number('create_num ', '累计发放数量')->default(1000)->required();
        $form->date('send_start_time ', '发放开始时间')->default(date('Y-m-d'));
        $form->date('send_end_time ', '发放结束时间')->default(date('Y-m-d', strtotime('+9year')));
        $form->date('use_start_time ', '使用开始时间')->default(date('Y-m-d'));
        $form->date('use_end_time ', '使用结束时间')->default(date('Y-m-d', strtotime('+9year')));
        $form->radio('status ', '状态')->options([0 => '禁用', 1 => '正常'])->default(1);
        $form->multipleSelect('for_goods ', '指定商品使用')->dataUrl(url('/admin/shopgoods/selectPage'));

        if ($isEdit) {

            $form->show('send_num ', '已发放数量');
            $form->show('use_num ', '已使用数量');
            $form->show('get_num ', '已领取数量');
            $form->show('del_num ', '已删除');

            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];

        if (!empty($searchData['name'])) {
            $where[] = ['name', 'like', '%' . $searchData['card_type'] . '%'];
        }

        if (!empty($searchData['card_type'])) {
            $where[] = ['card_type', 'eq', $searchData['card_type']];
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
        $search->select('card_type', '名称', 3)->options([1 => '折扣券', 2 => '购物券']);
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
        $table->text('name', '名称')->autoPost('', true)->getWrapper()->addStyle('max-width:120px;');
        $table->match('card_type', '类型')->options([1 => '折扣券', 2 => '购物券']);
        $table->match('type', '发放方式')->options([0 => '面额模板', 1 => '按用户发放', 2 => '注册', 3 => '邀请', 4 => '线下发放']);
        $table->show('money', '面额');
        $table->show('condition ', '使用条件');
        $table->fields('nums1', '预计/已发')->with(
            $table->show('create_num ', '累计发放数量'),
            $table->show('send_num ', '已发放数量')
        )->getWrapper()->addStyle('width:100px;');
        $table->fields('nums2', '使用/领取数量')->with(
            $table->show('use_num ', '已使用数量'),
            $table->show('get_num ', '已领取数量')
        )->getWrapper()->addStyle('width:100px;');
        $table->show('del_num ', '已删除数量');
        $table->fields('send_times', '发放开始/结束时间')->with(
            $table->show('send_start_time ', '发放开始时间'),
            $table->show('send_end_time ', '发放结束时间')
        )->getWrapper()->addStyle('width:100px;');

        $table->fields('use_times', '使用开始/结束时间')->with(
            $table->show('use_start_time ', '使用开始时间'),
            $table->show('use_end_time ', '使用结束时间')
        )->getWrapper()->addStyle('width:100px;');

        $table->show('create_time ', '添加时间')->getWrapper()->addStyle('width:160px;');
        $table->switchBtn('status ', '状态')->autoPost();
        $table->matches('for_goods ', '指定商品使用')->optionsData(ShopGoods::all(), 'name')->getWrapper()->addStyle('max-width:200px;');;
        $table->sortable('id,money,send_num,use_num,get_num');

        $table->getActionbar()
            ->btnEdit()
            ->btnDelete();
    }

    private function save($id = 0)
    {
        $data = request()->post();

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'card_type|类型' => 'require|number',
            'type|发放方式' => 'require|number',
            'money|面额' => 'require|float|gt:0',
            'create_num|累计发放数量' => 'require|number|gt:0',
        ]);

        if (true !== $result) {
            $this->error($result);
        }

        if ($data['card_type'] == 1 && ($data['money'] <= 0 || $data['money'] >= 10)) {
            $this->error('折扣应该在0折~10折之间');
        }

        if (!isset($data['for_goods'])) { //多选，如果没有选择任何值，参数里面将不存在这个键
            $data['for_goods'] = '';
        }

        return $this->doSave($data, $id);
    }
}
