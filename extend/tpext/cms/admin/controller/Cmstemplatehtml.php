<?php

namespace tpext\cms\admin\controller;

use tpext\think\App;
use think\Controller;
use tpext\builder\common\Builder;
use tpext\builder\traits\actions;
use tpext\cms\common\model\CmsChannel;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsContentPage;
use tpext\cms\common\model\CmsTemplateHtml as TemplateHtmlModel;

/**
 * Undocumented class
 * @title 模板页面管理
 */
class Cmstemplatehtml extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;
    use actions\HasEdit;
    use actions\HasDelete;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var TemplateHtmlModel
     */
    protected $dataModel;

    protected $pageTypes = ['index' => '首页', 'channel' => '栏目', 'content' => '详情', 'common' => '公共', 'single' => '单页', 'dynamic' => '动态'];

    protected function initialize()
    {
        $this->dataModel = new TemplateHtmlModel;
        $this->pageTitle = '模板页面管理';
        $this->pagesize = 6;
        $this->sortOrder = 'type,path';

        $this->selectSearch = 'path';
        $this->selectFields = 'id,path';
        $this->selectTextField = 'path';

        $this->pagesize = 9999;
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['path'])) {
            $where[] = ['path', 'like', '%' . $searchData['path'] . '%'];
        }
        if (!empty($searchData['name'])) {
            $where[] = ['name', 'like', '%' . $searchData['name'] . '%'];
        }

        $where[] = ['template_id', '=', input('template_id/d')];

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

        $search->text('path', '路径', 3)->maxlength(20);
        $search->text('name', '名称', 3)->maxlength(20);
        $search->hidden('template_id')->value(input('template_id/d'));
    }

    /**
     * Undocumented function
     *
     * @param Builder $builder
     * @param string $type index/add/edit/view
     * @return void
     */
    protected function creating($builder, $type = '')
    {
        //其他用户自定义初始化
        if ($type == 'index') {
            $template_id = input('template_id/d');
            $template = CmsTemplate::where('id', $template_id)->find();
            if (!$template) {
                $this->error('模板不存在');
            }
            $view_path = App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $template['view_path'];
            CmsTemplate::initPath($view_path);
            TemplateHtmlModel::scanPageFiles($template_id, $view_path);
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

        $table->raw('dir', '目录')->getWrapper()->addStyle('text-align:left;');
        $table->text('description', '描述')->autoPost()->mapClass(1, 'disabled', 'is_default');
        $table->raw('path', '路径')->to('<a target="_blank" href="/admin/cmstemplatehtml/edit?id={id}">{val}</a>');
        $table->match('type', '类型')->options($this->pageTypes + ['dir' => '目录'])
            ->mapClassGroup([['channel', 'success'], ['content', 'info'], ['common', 'warning'], ['single', 'purple'], ['index', 'danger'], ['dynamic', 'dark']]);
        $table->show('ext', '后缀');
        $table->show('size', '大小')->to('{val}kb');
        $table->show('conut', '统计');
        $table->show('filectime', '创建时间')->getWrapper()->addStyle('width:140px');
        $table->show('filemtime', '编辑时间')->getWrapper()->addStyle('width:140px');

        $table->sortable('size,name,path,ext,create_time,update_time');

        $table->getToolbar()
            ->btnAdd(url('add', ['template_id' => input('template_id/d')]))
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit('', '代码', 'btn-warning', 'mdi-table-edit', 'target="_blank"')
            ->btnLink('apply', url('/admin/cmstemplatehtml/apply', ['html_id' => '__data.pk__']), '绑定', 'btn-success', 'mdi-link-variant', 'title="绑定到栏目/内容"')
            ->btnDelete()
            ->mapClass([
                'edit' => ['hidden' => '__hi_edit__'],
                'apply' => ['hidden' => '__hi_apply__'],
                'delete' => ['hidden' => '__hi_delete__'],
            ]);

        $list = [];

        foreach ($data as &$d) {
            $view_path = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $d['path']);
            if (!is_file($view_path)) {
                $this->dataModel->where('id', $d['id'])->delete(); //真实文件不存在，删除记录
            }

            if ($d['type'] == 'channel' && $d['is_default'] == 0) {
                $d['conut'] = '栏目：' . CmsContentPage::where('html_type', 'channel')->where('html_id', $d['id'])->count();
            } else if ($d['type'] == 'content' && $d['is_default'] == 0) {
                $d['conut'] = '栏目：' . CmsContentPage::where('html_type', 'content')->where('html_id', $d['id'])->count();
            } else if ($d['type'] == 'single') {
                $d['conut'] = '文章：' . ($d['to_id'] ? 'id-' . $d['to_id'] : '未绑定');
            } else {
                $d['conut'] = '无';
            }

            $d['__dis_apply__'] = in_array($d['type'], ['common', 'dynamic']) || $d['is_default'];
            $d['__dis_delete__'] = $d['is_default'];

            $dirs = explode('/', $d['path']);

            if (count($dirs) > 3) {
                $dir = $dirs[2];
                if (!isset($list[$dir])) {
                    $list[$dir] = [
                        'dir' => $dir . '/',
                        'name' => '',
                        'description' => '存放' . $this->pageTypes[$dir] . '模板',
                        '__hi_edit__' => 1,
                        '__hi_apply__' => 1,
                        '__hi_delete__' => 1,
                        'is_default' => 1,
                        'size' => '--',
                        'type' => 'dir',
                    ];
                }
                $d['dir'] = '<span style="margin-left:30px"></span>' . '-|--' . $dirs[3] . '.html';
            } else {
                $d['dir'] = '--' . $dirs[2] . '.html';
            }

            $list[] = $d;
        }

        $data = $list;
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

        if ($isEdit) {
            $form->hidden('id');
            $view_path = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $data['path']);
            $form->show('path', ' ')->showLabel(false)->size(12, 12)->to('路径：{val}');
            $form->aceEditor('content', ' ')->setMode('html')->showLabel(false)->size(12, 12)->value(htmlentities(file_get_contents($view_path)));

            $form->butonsSizeClass('btn-md');
            $form->btnSubmit('提&nbsp;&nbsp;交', '12');
        } else {
            $template_id = input('template_id/d');
            $template = CmsTemplate::where('id', $template_id)->find();
            $form->hidden('template_id')->value($template_id);
            $form->show('view_path', '模板基础路径')->value('theme/' . $template['view_path']);
            $form->radio('type', '页面类型')->default('channel')->inline(false)->options(['channel' => '栏目页：' . '/channel/newpage-xx.html', 'content' => '详情页：' . '/content/newpage-xx.html', 'common' => '公共页：' . '/common/newpage-xx.html', 'single' => '单页：' . '/newpage-xx.html', 'dynamic' => '动态解析：' . '/dynamic/newpage-xx.html']);
            $form->text('name', '文件名称')->required()->default('newpage-xx')->help('格式：只能包含英文数字-_，不支持多级目录')->afterSymbol('.html');
            $form->textarea('description', '描述');
        }
    }

    /**
     * @title 绑定模板页面
     * @return mixed
     */
    public function apply()
    {
        $html_id = input('html_id/d');
        $page = $this->dataModel->where('id', $html_id)->find();
        if (!$page) {
            $this->error('模板页面不存在！');
        }

        $template = CmsTemplate::where('id', $page['template_id'])->find();
        if (!$template) {
            $this->error('模板类型不存在！');
        }

        if (request()->isGet()) {
            $builder = $this->builder('模板管理', '页面分配');
            $form = $builder->form();
            $form->show('path', '模板基础路径');
            $form->match('type', '页面类型')->options($this->pageTypes);
            if (str_replace('\\', '/', 'theme/' . $template['view_path'] . '/index.html') == str_replace('\\', '/', $page['path'])) {
                $form->show('tips', ' ')->value('首页不需要分配');
                $form->readonly();
            } else if (stripos($page['path'], 'common') !== false) {
                $form->show('tips', ' ')->value('公共页面不需要分配');
                $form->readonly();
            } else if (pathinfo($page['path'], PATHINFO_FILENAME) == 'default') {
                $form->show('tips', ' ')->value('栏目/详情默认模板不需要分配');
                $form->readonly();
            } else {
                if ($page['type'] == 'channel' || $page['type'] == 'content') {
                    $relation_ids = CmsContentPage::where('html_id', $html_id)->column('to_id');
                    $form->tree('relation_ids', '选择栏目')->optionsData(CmsChannel::where('is_show', 1)->field('id,name,parent_id')->select())->value($relation_ids)
                        ->help('哪些' . ($page['type'] == 'content' ? '内容' : '栏目') . '页使用该模板，可选择多个栏目')->required();
                } else if ($page['type'] == 'single') {
                    $form->select('to_id', '选择内容详情')->help('选择一篇文章')->dataUrl(url('/admin/cmscontent/selectpage'), '{id}#{title} — [所属栏目：{channel.full_name}]')->required();
                } else {
                    $form->show('tips', ' ')->value('未知页面类型');
                }
            }

            $form->fill($page);
            return $builder;
        }

        if ($page['type'] == 'single') {
            $to_id = input('post.to_id/d');
            $page->save(['to_id' => $to_id]);
            return $this->builder()->layer()->closeRefresh(1, '保存成功');
        }

        $relation_ids = input('post.relation_ids/a');

        if (empty($relation_ids)) {
            $this->error('请选择页面');
        }

        $activeIds = [];
        $active = null;
        $allIds = [];

        $pageList = CmsContentPage::where('html_id', $html_id)->select();

        foreach ($pageList as $prow) {
            $allIds[] = $prow['id'];
        }

        $success = 0;

        foreach ($relation_ids as $to_id) {
            if (empty($to_id)) {
                continue;
            }
            $active = null;
            foreach ($pageList as $prow) {
                if ($prow['to_id'] == $to_id) {
                    $active = $prow;
                    break;
                }
            }
            if ($active) {
                $activeIds[] = $active['id'];
                $success += 1;
            } else {
                $exist = CmsContentPage::where('template_id', $page['template_id'])
                    ->where('html_type', $page['type'])
                    ->where('to_id', $to_id)
                    ->where('html_id', '<>', $html_id)
                    ->find();

                if ($exist) {
                    $res = $exist->save([
                        'html_id' => $html_id,
                    ]);
                } else {
                    $perm = new CmsContentPage;
                    $res = $perm->save([
                        'to_id' => $to_id,
                        'template_id' => $page['template_id'],
                        'html_id' => $html_id,
                        'html_type' => $page['type'],
                    ]);
                }
                if ($res) {
                    $success += 1;
                }
            }
        }

        $delIds = array_diff($allIds, $activeIds);

        if (!empty($delIds)) {
            CmsContentPage::destroy(array_values($delIds));
        }

        if (!$success) {
            $this->error('保存失败');
        }

        return $this->builder()->layer()->closeRefresh(1, '保存成功');
    }

    /**
     * 保存数据
     *
     * @param integer $id
     * @return void
     */
    protected function save($id = 0)
    {
        $data = request()->only([
            'id',
            'content',
            'name',
            'description',
            'template_id',
            'type',
        ], 'post');

        $result = $this->validate($data, []);

        if (true !== $result) {

            $this->error($result);
        }

        if (isset($data['template_id'])) //新键页面
        {
            $data['name'] = str_replace('.html', '', $data['name']);

            $template = CmsTemplate::where('id', $data['template_id'])->find();

            $templatePath = str_replace(['\\', '/'], '/', $template['view_path']);

            $text = '新页面';
            $dir = $data['type'] . '/';

            if ($data['type'] == 'single') {
                $dir = '';
            }

            $path = 'theme/' . $templatePath . '/' . $dir . $data['name'] . '.html';

            $newTpl = CmsTemplate::getNewTpl();

            if ($data['type'] == 'content') {
                $text = CmsTemplate::getTemplatePart('tpl/content.html');
            } else if ($data['type'] == 'single') {
                $text = CmsTemplate::getTemplatePart('tpl/single.html');
            } else if ($data['type'] == 'channel') {
                $text = CmsTemplate::getTemplatePart('tpl/channel.html');
            } else if ($data['type'] == 'dynamic') {
                $text = CmsTemplate::getTemplatePart('tpl/dynamic.html');
            }

            $newFilePath = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

            if (!is_file($newFilePath)) {
                $newRes = @file_put_contents($newFilePath, str_replace('<!--__content__-->', $text, $newTpl));
                if (!$newRes) {
                    $this->error('创建新文件失败');
                }
            }

            $key = md5(strtolower($path));

            $data = array_merge([
                'path' => $path,
                'key' => $key,
                'ext' => 'html',
                'filectime' => date('Y-m-d H:i:s', filectime($newFilePath)),
                'filemtime' => date('Y-m-d H:i:s', filemtime($newFilePath)),
                'size' => round(filesize($newFilePath) / 1024, 2),
            ], $data);

            return $this->doSave($data, $id);
        }

        $page = $this->dataModel->where('id', $id)->find();

        if (!$page) {
            $this->error('保存失败，页面不存在！');
        }

        $file_path = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $page['path']);

        $res = @file_put_contents($file_path, $data['content']);

        if (!$res) {
            $this->error('保存失败');
        }

        $page->save([
            'version' => $page['version'] + 1,
        ]);

        $this->success('保存成功，页面即将刷新~', null, '', 1);
    }
}
