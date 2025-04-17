<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\cms\common\Module;
use tpext\builder\traits\actions;
use tpext\manager\common\logic\DbLogic;
use tpext\cms\common\model\CmsContentField as CmsContentFieldModel;

/**
 * Undocumented class
 * @title 内容字段管理
 */
class Cmscontentfield extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;

    /**
     * Undocumented variable
     *
     * @var DbLogic
     */
    protected $dbLogic;

    protected $tableName = '';

    protected function initialize()
    {
        $this->pageTitle = '内容字段管理';
        $this->indexText = '字段列表';

        $this->pk = 'COLUMN_NAME';
        $this->dbLogic = new DbLogic;
        $this->tableName = $this->dbLogic->getPrefix() . 'cms_content';
        $this->pagesize = 9999; //不产生分页
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [], $isExporting = false)
    {
        $table = $this->table;

        $table->show('COLUMN_NAME', '字段名');
        $table->show('COLUMN_COMMENT', '字段注释');
        $table->match('DATA_TYPE', '数据类型')->options(CmsContentFieldModel::$FIELD_TYPES);
        $table->show('LENGTH', '长度')->default('-');
        $table->show('NUMERIC_SCALE', '小数点')->default('-');
        $table->show('COLUMN_DEFAULT', '默认值');
        $table->match('IS_NULLABLE', '可为NULL')->yesOrNo();
        $table->matches('ATTR', '属性')->options(['index' => '索引', 'unique' => '唯一', 'unsigned' => '非负']);

        $table->getToolbar()
            ->btnAdd()->barAddAttr('data-layer-size="1200px,610px"')
            ->btnLink(url('trash'), '字段回收站', 'btn-danger', 'mdi-delete-variant');

        $table->getActionbar()
            ->btnEdit()->barAddAttr('data-layer-size="1200px,610px"')
            ->btnDelete()
            ->mapClass([
                'edit' => [
                    'disabled' => function ($row) {
                        return $row['IS_PROTECTED'];
                    }
                ],
                'delete' => [
                    'disabled' => function ($row) {
                        return $row['IS_PROTECTED'];
                    }
                ]
            ]);

        $table->useCheckbox(false);
    }

    /**
     * Undocumented function
     * @title 回收站
     *
     * @return mixed
     */
    public function trash()
    {
        $builder = $this->builder($this->pageTitle, '回收站');
        $table = $builder->table();

        $table->match('type', '类型')->options(['table' => '表', 'field' => '字段'])->mapClassGroup([['table', 'success'], ['field', 'info']]);
        $table->raw('name', '名称');
        $table->show('comment', '注释');
        $table->show('delete_time', '删除时间')->getWrapper()->addStyle('width:180px');

        $data = [];

        $deletedFields = $this->dbLogic->getDeletedFields($this->tableName);

        foreach ($deletedFields as $field) {
            $arr = explode('_del_at_', $field['COLUMN_NAME']);
            $data[] = [
                'id' => $field['COLUMN_NAME'],
                'name' => $this->tableName . '.' . $field['COLUMN_NAME'],
                'comment' => $field['COLUMN_COMMENT'],
                'type' => 'field',
                'delete_time' => date('Y-m-d H:i:s', $arr[1]),
            ];
        }

        $table->fill($data);

        $table->getActionbar()
            ->btnDelete(url('destroy'), '删除', 'btn-danger', 'mdi-delete', 'title="彻底删除表或字段"', '删除后数据不可恢复，确定要执行此操作吗？')
            ->btnPostRowid('recovery', url('recovery'), '恢复', 'btn-success', 'mdi-backup-restore', 'title="恢复表或字段"');

        $table->useCheckbox(false);
        $table->useToolbar(false);

        if (request()->isAjax()) {
            return $table->partial()->render();
        }

        return $builder->render();
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
        $form->defaultDisplayerSize(12, 12);

        $form->left(7)->with(function () use ($form) {
            $form->text('COLUMN_NAME', '字段名')->help('英文字母开头，英文字母或数字下或划线组成')->required();
            $form->text('COLUMN_COMMENT', '字段注释')->required();
            $form->text('COLUMN_DEFAULT', '默认值');
        });

        $form->left(5)->with(function () use ($form) {

            $form->select('displayer_type', '输入类型')->default('auto')->required()->options(CmsContentFieldModel::$displayerTypes)
                ->when(['text'])->with(
                    $form->number('LENGTH', '长度')->default(55)->help('文本最大长度')
                )->when(['textarea'])->with(
                    $form->number('LENGTH', '长度')->default(255)->help('文本最大长度')
                )->when(['float'])->with(
                    $form->number('NUMERIC_SCALE', '小数位')->help('保留小数点位数')
                )->when(['number', 'radio', 'select'])->with(
                    $form->switchBtn('index', '索引')->help('索引加快查询速度')
                )->when(['number', 'float'])->with(
                    $form->switchBtn('unsigned', '非负')->help('只能存大于0的数值')
                )->when(['radio', 'select'])->with(
                    $form->select('DATA_TYPE', '数据类型')->options([
                        'int' => 'int(数字)',
                        'varchar' => 'varchar(不定长文本)',
                    ])->default('varchar')->required()
                )
                ->when(['number'])->with(
                    $form->select('DATA_TYPE', '数据类型')->options([
                        'int' => 'int(数字)',
                        'bigint' => 'bigint(数字20位)',
                    ])->default('int')->required()
                )
                ->when(['none'])->with(
                    $form->select('DATA_TYPE', '数据类型')->options(CmsContentFieldModel::$FIELD_TYPES)->default('varchar')
                )->when(['select', 'radio', 'checkbox', 'multipleSelect'])->with(
                    $form->textarea('options', '选项')->required()->help('可选择的项，每行一个，格式：key : value或value')
                );
        });
    }

    /**
     * 保存数据
     *
     * @param string $field
     * @return mixed
     */
    protected function save($id = '')
    {
        $data = request()->post();

        if (!isset($data['IS_NULLABLE'])) {
            $data['IS_NULLABLE'] = '0';
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]+$/', $data['COLUMN_NAME'])) {
            $this->error('字段名英文字母开头，只能包含英文字母、数字、下划线');
        }

        if (in_array($data['COLUMN_NAME'], ['content', 'attachments'])) {
            $this->error('字段名不能为：' . $data['COLUMN_NAME'] . '，因为它是扩展字段');
        }

        $data = $this->judgeDataType($data);

        $res = 0;

        if ($id) {
            $res = $this->dbLogic->changeField($this->tableName, $id, $data);
        } else {
            $res = $this->dbLogic->addField($this->tableName, $data);
        }

        if (!$res) {
            $this->error(__blang('bilder_save_failed'));
        }

        $fieldExists = CmsContentFieldModel::where('name', $data['COLUMN_NAME'])->find();
        if (!$fieldExists) {
            $fieldExists = new CmsContentFieldModel();
        }
        $fieldExists->save([
            'name' => $data['COLUMN_NAME'],
            'data_type' => $data['DATA_TYPE'],
            'length' => $data['LENGTH'] ?? 0,
            'numerc_scale' => $data['NUMERIC_SCALE'] ?? 0,
            'default' => $data['COLUMN_DEFAULT'],
            'displayer_type' => $data['displayer_type'],
            'options' => isset($data['options']) ? $data['options'] : '',
        ]);

        return $this->builder()->layer()->closeRefresh(1, __blang('bilder_save_succeeded'));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function judgeDataType($data)
    {
        if (in_array($data['displayer_type'], ['date', 'datetime', 'float'])) {
            $data['DATA_TYPE'] = $data['displayer_type'];
        }
        if (in_array($data['displayer_type'], ['int', 'none'])) {
            //
        } else if (in_array($data['displayer_type'], ['switchBtn', 'yesOrNo'])) {
            $data['DATA_TYPE'] = 'tinyint';
            $data['LENGTH'] = 1;
            $data['unsigned'] = 1;
        } else if ($data['displayer_type'] == 'editor') {
            $data['DATA_TYPE'] = 'text';
        } else {
            $data['DATA_TYPE'] = 'varchar';
        }

        $data['ATTR'] = [];
        if (isset($data['index']) && $data['index'] == 1) {
            $data['ATTR']['index'] = 'index';
        }
        if (isset($data['unsigned']) && $data['unsigned'] == 1) {
            $data['ATTR']['unsigned'] = 'unsigned';
        }

        if ($data['DATA_TYPE'] == 'text') {
            $data['IS_NULLABLE'] = 1;
            $data['COLUMN_DEFAULT'] = 'NULL';
        }

        if (in_array($data['displayer_type'], ['date', 'datetime'])) {
            if (strtoupper($data['COLUMN_DEFAULT']) == 'NULL') {
                $data['IS_NULLABLE'] = 1;
                $data['COLUMN_DEFAULT'] = 'NULL';
            } else {
                $data['IS_NULLABLE'] = 0;
            }
        }

        return $data;
    }

    public function add()
    {
        if (request()->isGet()) {

            $builder = $this->builder($this->pageTitle, $this->addText ?: __blang('bilder_page_add_text'), 'add');
            $form = $builder->form();
            $data = [];
            $this->form = $form;
            $this->isEdit = 0;
            $this->buildForm($this->isEdit, $data);
            $form->fill($data);
            $form->method('post');

            return $builder->render();
        }

        $this->checkToken();

        return $this->save();
    }

    /**
     * 生成数据，如数据不是从`$this->dataModel`得来时，可重写此方法
     * 比如使用db()助手方法、多表join、或以一个自定义数组为数据源
     *
     * @param array $where
     * @param string $sortOrder
     * @param integer $page
     * @param integer $total
     * @return array|\think\Collection|\Generator
     */
    protected function buildDataList($where = [], $sortOrder = '', $page = 1, &$total = -1)
    {
        $data = $this->dbLogic->getFields($this->tableName, 'COLUMN_NAME,COLUMN_TYPE,COLUMN_DEFAULT,COLUMN_COMMENT,IS_NULLABLE,NUMERIC_SCALE,NUMERIC_PRECISION,CHARACTER_MAXIMUM_LENGTH,DATA_TYPE');
        $total = count($data);

        $protectedFields = $this->getProtectedFields();

        foreach ($data as &$field) {
            if ($this->dbLogic->isInteger($field['DATA_TYPE']) || $this->dbLogic->isDecimal($field['DATA_TYPE']) || $this->dbLogic->isChartext($field['DATA_TYPE'])) {
                $field['LENGTH'] = preg_replace('/^\w+\((\d+).+?$/', '$1', $field['COLUMN_TYPE']);
            }

            $keys = $this->dbLogic->getKeys($this->tableName, $field['COLUMN_NAME']);

            $field['ATTR'] = '';

            $ATTR = [];

            if (strpos($field['COLUMN_TYPE'], 'unsigned')) {
                $ATTR['unsigned'] = 'unsigned';
            }

            foreach ($keys as $key) {
                if (strtoupper($key['INDEX_NAME']) == 'PRIMARY') {
                    $ATTR['index'] = 'index';
                    continue;
                }

                if ($key['NON_UNIQUE'] == 1) {
                    $ATTR['index'] = 'index';
                } else {
                    $ATTR['unique'] = 'unique';
                }
            }

            $field['ATTR'] = implode(',', $ATTR);
            $field['DATA_TYPE'] = strtolower($field['DATA_TYPE']);
            $field['IS_NULLABLE'] = $field['IS_NULLABLE'] == 'YES';
            $field['IS_PROTECTED'] = in_array($field['COLUMN_NAME'], $protectedFields);

            if (is_null($field['COLUMN_DEFAULT'])) {
                $field['COLUMN_DEFAULT'] = 'NULL';
            } else {
                $field['COLUMN_DEFAULT'] = trim($field['COLUMN_DEFAULT'], "'");
            }

            if (!$field['IS_PROTECTED']) {
                $fieldExists = CmsContentFieldModel::where('name', $field['COLUMN_NAME'])->find();
                if (!$fieldExists) {
                    $fieldExists = new CmsContentFieldModel();
                    $fieldExists->save([
                        'name' => $field['COLUMN_NAME'],
                        'numerc_scale' => $field['NUMERIC_SCALE'],
                        'data_type' => $field['DATA_TYPE'],
                        'default' => $field['COLUMN_DEFAULT'],
                    ]);
                }
            }
        }

        unset($keys, $field);

        return $data;
    }

    public function edit()
    {
        $id = input('id');

        if (request()->isGet()) {

            $builder = $this->builder($this->pageTitle, $this->editText ?: __blang('bilder_page_edit_text'), 'edit');

            $field = $this->getFieldInfo($id);

            if (!$field) {
                return $builder->layer()->close(0, __blang('bilder_data_not_found'));
            }

            $form = $builder->form();
            $this->form = $form;
            $this->isEdit = 1;
            $this->buildForm($this->isEdit, $field);
            $form->fill($field);
            $form->method('put');

            return $builder->render();
        }

        $this->checkToken();

        return $this->save($id);
    }

    /**
     * Undocumented function
     * @title 恢复已删除的表或字段
     *
     * @return mixed
     */
    public function recovery()
    {
        $ids = input('post.ids', '');
        $ids = array_filter(explode(',', $ids), 'strlen');

        if (empty($ids)) {
            $this->error('参数有误');
        }

        $res = 0;
        foreach ($ids as $id) {
            if ($this->dbLogic->recoveryField($this->tableName, $id)) {
                $res += 1;
            }
        }

        if ($res) {
            $this->success('成功恢复', '', ['script' => '<script>parent.$(".search-refresh").trigger("click");</script>']);
        } else {
            $this->error('恢复失败' . $this->dbLogic->getErrorsText());
        }
    }

    /**
     * Undocumented function
     * @title 彻底删除表或字段
     * @return mixed
     */
    public function destroy()
    {
        $ids = input('post.ids', '');
        $ids = array_filter(explode(',', $ids), 'strlen');

        if (empty($ids)) {
            $this->error('参数有误');
        }

        $res = 0;
        foreach ($ids as $id) {
            if ($this->dbLogic->dropField($this->tableName, $id)) {
                $res += 1;
                $this->dataModel->where('name', $id)->delete();
            }
        }

        if ($res) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败' . $this->dbLogic->getErrorsText());
        }
    }

    public function delete()
    {
        $this->checkToken();

        $ids = input('post.ids', '');
        $ids = array_filter(explode(',', $ids), 'strlen');
        if (empty($ids)) {
            $this->error(__blang('bilder_parameter_error'));
        }
        $res = 0;
        foreach ($ids as $id) {
            $field = $this->getFieldInfo($id);
            $field['COLUMN_NAME'] .= '_del_at_' . time();
            $field['IS_NULLABLE'] = 1;
            if ($this->dbLogic->changeField($this->tableName, $id, $field)) {
                $res += 1;
            }
        }

        if ($res) {
            $this->success(__blang('bilder_delete_{:num}_records_succeeded', ['num' => $res]));
        } else {
            $this->error(__blang('bilder_delete_failed'));
        }
    }

    /**
     * @param string $field
     * @return array
     */
    protected function getFieldInfo($name)
    {
        $field = $this->dbLogic->getFieldInfo($this->tableName, $name);
        if (!$field) {
            return [];
        }
        $ATTR = [];
        if (strpos($field['COLUMN_TYPE'], 'unsigned')) {
            $ATTR['unsigned'] = 'unsigned';
        }
        $keys = $this->dbLogic->getKeys($this->tableName, $name);
        foreach ($keys as $key) {
            if (strtoupper($key['INDEX_NAME']) == 'PRIMARY') {
                $ATTR['index'] = 'index';
                continue;
            }

            if ($key['NON_UNIQUE'] == 1) {
                $ATTR['index'] = 'index';
            } else {
                $ATTR['unique'] = 'unique';
            }
        }

        $field['ATTR'] = $ATTR;
        $field['IS_NULLABLE'] = $field['IS_NULLABLE'] == 'YES';

        $fieldExists = CmsContentFieldModel::where('name', $field['COLUMN_NAME'])->find();

        if ($fieldExists) {
            $field['displayer_type'] = $fieldExists['displayer_type'];
            $field['options'] = $fieldExists['options'];
        }

        return $field;
    }

    /**
     * @return array
     */
    protected function getProtectedFields()
    {
        $sqlFile = Module::getInstance()->getRoot() . 'data' . DIRECTORY_SEPARATOR . 'install.sql';
        $content = file_get_contents($sqlFile);

        preg_match('/CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content`\s*\((.+?)\)\s*ENGINE=InnoDB/is', $content, $mch);
        $cms_content_create = $mch[1];
        preg_match_all('/`(\w+)`.+?COMMENT/is', $cms_content_create, $matches);

        $fields = [];
        foreach ($matches[1] as $i => $match) {
            $fields[] = $match;
        }

        return $fields;
    }
}
