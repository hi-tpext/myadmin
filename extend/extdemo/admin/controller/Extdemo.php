<?php
namespace extdemo\admin\controller;

use extdemo\admin\model\Extdemo as ExtdemoModel;
use think\Controller;
use tpext\builder\traits\actions\HasBase;
use tpext\builder\traits\actions\HasIndex;

/**
 * Undocumented class
 * @title 扩展范例
 */
class Extdemo extends Controller
{
    use HasBase;
    use HasIndex;

    /**
     * Undocumented variable
     *
     * @var ExtdemoModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new ExtdemoModel;

        $this->pageTitle = '范例';
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
        $table->show('create_time', '创建时间');
        $table->show('update_time', '更新时间');

        $table->getToolbar()->html('<label class="label label-success">能看到此条文字说明成功了</label>');

        $table->useActionbar(false);

        $table->useCheckbox(false);
    }
}
