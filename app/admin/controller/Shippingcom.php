<?php

namespace app\admin\controller;

use app\common\model\ShippingCom as ShippingComModel;
use think\Controller;
use tpext\builder\traits\actions;

/**
 * Undocumented class
 * @title 快递公司
 */
class Shippingcom extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var ShippingComModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new ShippingComModel;

        $this->pageTitle = '快递公司';
        $this->sortOrder = 'id ascs';
        $this->pagesize = 14;

        $this->selectSearch = 'name|code';
        $this->selectIdField = 'code';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];

        if (!empty($searchData['kwd'])) {
            $where[] = ['name|code', 'like', '%' . $searchData['kwd'] . '%'];
        }

        if (isset($searchData['enable']) && $searchData['enable'] != '') {
            $where[] = ['enable', '=', $searchData['enable']];
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

        $search->text('kwd', '名称/编码', 3)->maxlength(55);
        $search->select('enable', '启用状态')->options([0 => '未启用', 1 => '已启用']);
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
        $table->show('name', '公司名称');
        $table->show('code', '编码');
        $table->switchBtn('enable', '启用')->autoPost();

        $table->getToolbar()
            ->btnRefresh();

        $table->sortable('name');
        $table->useActionbar(false);
    }
}
