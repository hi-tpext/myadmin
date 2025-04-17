<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\cms\common\Module;
use tpext\builder\traits\actions;
use tpext\manager\common\logic\DbLogic;
use tpext\cms\common\model\CmsContentField;
use tpext\cms\common\model\CmsContentModelField;
use tpext\cms\common\model\CmsContentModel as CmsContentModelModel;

/**
 * Undocumented class
 * @title 内容模型管理
 */
class Cmscontentmodel extends Controller
{
    use actions\HasBase;
    use actions\HasIndex;
    use actions\HasAutopost;
    use actions\HasAdd;
    use actions\HasEdit;
    use actions\HasView;
    use actions\HasDelete;

    /**
     * Undocumented variable
     *
     * @var DbLogic
     */
    protected $dbLogic;

    protected $tableName = '';

    protected $extendFields = [
        ['name' => 'content', 'comment' => '正文内容'],
        ['name' => 'attachments', 'comment' => '附件'],
    ];

    protected function initialize()
    {
        $this->dataModel = new CmsContentModelModel;

        $this->pageTitle = '内容模型管理';
        $this->sortOrder = 'sort asc,id asc';
        $this->pagesize = 8;

        $this->selectSearch = 'name';
        $this->selectFields = 'id,name';
        $this->selectTextField = 'name';


        $this->dbLogic = new DbLogic;
        $this->tableName = $this->dbLogic->getPrefix() . 'cms_content';
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [], $isExporting = false)
    {
        $table = $this->table;

        $tableFields = $this->dbLogic->getFields(
            $this->tableName,
            'COLUMN_NAME as name,COLUMN_COMMENT as comment',
            "COLUMN_NAME not in('id','admin_id','create_time','update_time','delete_time')"
        );

        $tableFields = array_merge($tableFields, $this->extendFields);

        $table->show('name', '模型名称')->getWrapper()->addStyle('min-width:160px');
        $table->matches('fields', '字段')->optionsData($tableFields, '{name}({comment})', 'name')->getWrapper()->addStyle('max-width:600px');
        $table->text('sort', '排序')->autoPost()->getWrapper()->addStyle('width:80px');

        $table->getToolbar()
            ->btnAdd()->barAddAttr('data-layer-size="1200px,auto"');

        $table->getActionbar()
            ->btnEdit()->barAddAttr('data-layer-size="1200px,auto"')
            ->btnDelete()
            ->mapClass([
                'delete' => [
                    'disabled' => function ($row) {
                        return $row['id'] == 1;
                    }
                ]
            ]);

        $table->useCheckbox(false);

        $this->builder()->addStyleSheet('
        .table > tbody > tr > td .row-fields
        {
            white-space:normal;
        }
        ');
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

        $tableFields = $this->dbLogic->getFields(
            $this->tableName,
            'COLUMN_NAME as name,COLUMN_COMMENT as comment',
            "COLUMN_NAME not in('id','title','channel_id','admin_id','model_id','create_time','update_time','delete_time')"
        );

        $tableFields = array_merge($tableFields, $this->extendFields);

        $allFields = CmsContentField::select();

        $modelFields = [];
        if ($isEdit) {
            $modelFields = CmsContentModelField::where('model_id', $data['id'])->select();
        }

        $protectedFields = $this->getProtectedFields();

        foreach ($tableFields as &$tFeield) {
            foreach ($modelFields as $mField) {
                if ($tFeield['name'] == $mField['name']) {
                    $tFeield['is_use'] = 1;
                    $tFeield['displayer_type'] = $mField['displayer_type'];
                    $tFeield['position'] = $mField['position'];
                    $tFeield['comment'] = $mField['comment'];
                    $tFeield['help'] = $mField['help'];
                    $tFeield['rules'] = $mField['rules'];
                    break;
                }
            }
            $tFeield['protected'] = 0;
            if (in_array($tFeield['name'], $protectedFields)) {
                $tFeield['protected'] = 1;
                $tFeield['displayer_type'] = 'auto';
                $tFeield['position'] = 'auto';
                if (in_array($tFeield['name'], ['link', 'mention_ids'])) {
                    $tFeield['position'] = 'extend';
                } else if (in_array($tFeield['name'], ['tags', 'keywords', 'description'])) {
                    $tFeield['position'] = 'main_left';
                } else {
                    $tFeield['position'] = 'main_right';
                }
            }
            if (!isset($tFeield['position']) && in_array($tFeield['name'], ['content', 'attachments'])) {
                $tFeield['position'] = $tFeield['name'] == 'content' ? 'main_left' : 'extend';
            }
            if (!isset($tFeield['displayer_type'])) {
                foreach ($allFields as $aField) {
                    if ($tFeield['name'] == $aField['name']) {
                        $tFeield['displayer_type'] = $aField['displayer_type'];
                        break;
                    }
                }
                if (!isset($tFeield['displayer_type'])) {
                    if ($tFeield['name'] == 'content') {
                        $tFeield['displayer_type'] = 'editor';
                    } else if ($tFeield['name'] == 'attachments') {
                        $tFeield['displayer_type'] = 'files';
                    }
                }
            }
        }

        $form->text('name', '模型名称')->required()->maxlength(5)->size(12, 4);
        $form->items('use_fields', '字段')->required()->with(function () use ($form) {
            $form->show('name', '字段名');
            $form->text('comment', '字段说明')->required();
            $form->switchBtn('is_use', '使用字段');
            $form->select('displayer_type', '输入类型')->required()
                ->mapClass(1, 'disabled', 'protected')
                ->options(CmsContentField::$displayerTypes)
                ->afterOptions(['auto' => '默认']);
            $form->select('position', '位置')
                ->required()
                ->default('main_right')
                ->options(['main_left' => '[基本信息]左侧', 'main_right' => '[基本信息]右侧', 'extend' => '[扩展信息]', 'auto' => '默认'])
                ->mapClassGroup([[1, 'disabled', 'protected'], ['content', 'disabled', 'name']]);
            $form->text('help', '帮助信息');
            $form->checkbox('rules', '验证规则')->options(['required' => '必填']);
        })->canNotAddOrDelete()->dataWithId($tableFields, 'name')->size(12, 12)
            ->help('系统字段只可选择使用或不使用，不可修改输入类型和位置。标题和所属栏目必须项，不在上面列出。'
                . '字段说明和帮助信息可修改，同一字段在不同模型可以有不同的用途。帮助信息为输入区域下放的提示信息。');

        if ($isEdit) {
            $form->hidden('id');
        }
    }

    /**
     * 保存数据
     *
     * @param string $id
     * @return mixed
     */
    protected function save($id = 0)
    {
        $data = request()->post();

        $result = $this->validate($data, [
            'name|模型名称' => 'require',
            'use_fields|字段' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        $res = 0;

        $useFields = $data['use_fields'];

        $fieldNames = [];

        foreach ($useFields as $name => $field) {
            if ($field['is_use'] == 1) {
                $fieldNames[] = $name;
            }
        }

        $data['fields'] = implode(',', $fieldNames);

        if ($id) {
            $model = $this->dataModel->where('id', $id)->find();
            $res = $model && $model->save($data);
        } else {
            $model = new CmsContentModelModel;
            $res = $model->save($data);
            if ($res) {
                $id = $model['id'];
            }
        }

        if (!$res) {
            $this->error(__blang('bilder_save_failed'));
        }

        $modelFieldNames = CmsContentField::column('name');
        $protectedFields = $this->getProtectedFields();

        foreach ($useFields as $name => $field) {
            if ($field['is_use'] == 0) {
                continue;
            }
            $exist = CmsContentModelField::where('model_id', $id)->where('name', $name)->find();
            if (!$exist) {
                $exist = new CmsContentModelField;
            }
            if ($name == 'attachments' && $field['position'] == 'auto') {
                $field['position'] = 'extend';
            }
            if (in_array($name, $protectedFields)) {
                if (in_array($name, ['link', 'mention_ids'])) {
                    $field['position'] = 'extend';
                } else if (in_array($name, ['tags', 'keywords', 'description'])) {
                    $field['position'] = 'main_left';
                } else {
                    $field['position'] = 'main_right';
                }
            }
            $exist->save([
                'model_id' => $id,
                'name' => $name,
                'comment' => $field['comment'],
                'help' => $field['help'],
                'displayer_type' => $field['displayer_type'] ?? 'auto',
                'position' => $field['position'] ?? 'auto',
                'rules' => isset($field['rules']) ? implode(',', $field['rules']) : '',
                'is_custom' => in_array($name, $modelFieldNames) ? 1 : 0,
            ]);
        }

        if (count($fieldNames) == 0) {
            $this->error('至少要选择一个字段');
        }

        CmsContentModelField::where('model_id', $id)->whereNotIn('name', $fieldNames)->delete();

        return $this->builder()->layer()->closeRefresh(1, __blang('bilder_save_succeeded'));
    }

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
