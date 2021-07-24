<?php

namespace app\admin\controller;

use app\common\model\ShippingArea as ShippingAreaModel;
use app\common\model\ShippingAreaItem;
use app\common\model\ShippingCom;
use think\Controller;
use think\facade\Db;
use tpext\areacity\api\model\Areacity;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 运费模板
 */
class Shippingarea extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var ShippingAreaModel
     */
    protected $dataModel;

    /**
     * Undocumented variable
     *
     * @var ShippingCom
     */
    protected $shippingComModel;

    /**
     * Undocumented variable
     *
     * @var ShippingAreaItem
     */
    protected $areaItemModel;

    protected function initialize()
    {
        $this->dataModel = new ShippingAreaModel;
        $this->shippingComModel = new ShippingCom;
        $this->areaItemModel = new ShippingAreaItem;

        $this->pageTitle = '运费模板';
        $this->sortOrder = 'id desc';
        $this->pagesize = 14;
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['com_codes'])) {
            $where[] = ['com_codes', 'like', '%,' . $searchData['com_codes'] . ',%'];
        }

        if (isset($searchData['enable']) && $searchData['enable'] != '') {
            $where[] = ['enable', '=', $searchData['enable']];
        }

        if (!empty($searchData['province'])) {

            $pids = ShippingAreaItem::where(['province' => $searchData['province']])->column('area_id');

            $where[] = ['id', 'in', $pids];

            if (!empty($searchData['city'])) {

                $cids = ShippingAreaItem::where(['city' => $searchData['city']])->column('area_id');

                $where[] = ['id', 'in', $cids];

                if (!empty($searchData['area'])) {

                    $aids = ShippingAreaItem::where(['area' => $searchData['area']])->column('area_id');

                    $where[] = ['id', 'in', $aids];

                    if (!empty($searchData['town'])) {
                        $tids = ShippingAreaItem::where(['town' => $searchData['town']])->column('area_id');
                        $where[] = ['id', 'in', $tids];
                    }
                }
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
        $search->select('com_codes', '快递公司')->dataUrl(url('/admin/shippingcom/selectPage'));
        $search->select('enable', '启用状态')->options([0 => '未启用', 1 => '已启用']);
        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')->withNext(
                    $search->select('town', '乡镇')->dataUrl(url('api/areacity/town'), 'ext_name')
                )
            )
        );
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

        $table->show('com_names', '公司名称');
        $table->raw('pcat', '区域');
        $table->text('first_weight', '首重重量')->autoPost()->getWrapper()->addStyle('width:120px;');
        $table->text('money', '首重费用')->autoPost()->getWrapper()->addStyle('width:120px;');
        $table->text('second_weight', '续重重量')->autoPost()->getWrapper()->addStyle('width:120px;');
        $table->text('add_money', '续重费用')->autoPost()->getWrapper()->addStyle('width:120px;');
        $table->switchBtn('enable', '启用')->autoPost()->getWrapper()->addStyle('width:100px;');

        $table->sortable('name');
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

        $itemList = $isEdit ? $this->areaItemModel->where(['area_id' => $data['id']])->select() : [];

        $form->fields('首重')->required()->with(
            $form->text('first_weight', '重量', 6)->size(12, 12)->default(1000)->afterSymbol('克'),
            $form->text('money', '费用', 6)->size(12, 12)->default(10)->afterSymbol('元')
        );

        $form->fields('续重')->with(
            $form->text('second_weight', '重量', 6)->size(12, 12)->default(0)->afterSymbol('克'),
            $form->text('add_money', '费用', 6)->size(12, 12)->default(0)->afterSymbol('元')
        )->help('续重不填则只收取首重费用');

        $form->switchBtn('enable', '启用')->default(1);

        $form->multipleSelect('com_codes', '快递公司')->required()->size(2, 10)->dataUrl(url('/admin/shippingcom/selectPage'), 'name', 'code')->help('从已启用的快递公司总选择，可以多选');

        $form->items('pcat_list', '地区')->required()->dataWithId($itemList)->size(12, 12)->help('省份不选则对全国有效，市不选则全省有效，以此类推。<br/>查找费用时从下级往上找，例如有两条规则:1、云南省[首重:1000克,费用:10元],２、云南省-昆明市[首重:1000克,费用:8元]。云南其他地区没有设置[市/区]具体规则。<br/>那么昆明市按规则2，云南其他地区按规则1');

        $form->select('province', '省', 3)->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $form->select('city', '市', 3)->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $form->select('area', '区', 3)->dataUrl(url('api/areacity/area'), 'ext_name')->withNext(
                    $form->select('town', '街道', 3)->dataUrl(url('api/areacity/town'), 'ext_name')
                )
            )
        );

        $form->itemsEnd();
    }

    private function save($id = 0)
    {
        $data = request()->only([
            'com_codes',
            'pcat_list',
            'first_weight',
            'money',
            'second_weight',
            'add_money',
            'enable',
        ], 'post');

        $result = $this->validate($data, [
            'com_codes|快递公司' => 'require|array',
            'pcat_list|地区' => 'require|array',
            'first_weight|首重首重' => 'require|number',
            'money|首重费用' => 'require|float',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        $data['com_codes'] = ',' . implode(',', $data['com_codes']) . ',';

        $pcat_list = $data['pcat_list'];
        unset($data['pcat_list']);

        if ($id) {
            $res = $this->dataModel->update($data, ['id' => $id]);
        } else {
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $id = $res = Db::name('shipping_area')->insertGetId($data);
        }

        $itemKey = [];
        $errors = [];

        Db::startTrans();

        foreach ($pcat_list as $key => $pdata) {
            $itenm_key = $pdata['province'] . $pdata['city'] . $pdata['area'] . $pdata['town'];

            $pdata['area_id'] = $id;
            $pdata['com_codes'] = $data['com_codes'];

            $is_del = isset($pdata['__del__']) && $pdata['__del__'] == 1;
            $is_add = strpos($key, '__new__') !== false;

            if (!$is_del && in_array($itenm_key, $itemKey)) {
                $errors[] = '地区存在重复，请修改';
                if ($is_add) {
                    continue;
                }
            }

            if ($is_add) {
                $res = $this->areaItemModel->create($pdata);
                if ($res) {
                    $itemKey[] = $itenm_key;
                } else {
                    $errors[] = '保存出错';
                }
            } else {
                if ($is_del) {
                    $this->areaItemModel->destroy($key);
                } else {
                    $res = $this->areaItemModel->update($pdata, ['id' => $key]);
                    if ($res) {
                        $itemKey[] = $itenm_key;
                    } else {
                        $errors[] = '保存出错';
                    }
                }
            }
        }

        Db::commit();

        if (!$res) {
            $this->error('保存失败');
        }

        if (!empty($errors)) {
            $script = '<script>parent.$(".search-refresh").trigger("click")</script>'; //刷新列表页面
            $this->error(implode('<br>', $errors), url('edit', ['id' => $id]), ['script' => $script], 3);
        }

        return $this->builder()->layer()->closeRefresh(1, '保存成功');
    }
}
