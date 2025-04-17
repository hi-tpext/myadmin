<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\builder\traits\actions;
use tpext\cms\common\model\CmsTemplateHtml;
use tpext\cms\common\model\CmsTemplate;
use tpext\think\App;
use tpext\cms\common\Render;

/**
 * Undocumented class
 * @title 静态资源管理
 */
class Cmstemplatestatic extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;

    protected function initialize()
    {
        $this->pageTitle = '静态资源管理';
        $this->pagesize = 6;
        $this->pk = 'path';

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

    protected function buildDataList($where = [], $sortOrder = '', $page = 1, &$total = -1)
    {
        $template_id = input('template_id/d');
        $template = CmsTemplate::where('id', $template_id)->find();
        if (!$template) {
            $this->error('模板不存在');
        }

        $data = CmsTemplateHtml::scanStaticFiles($template_id,  App::getRootPath() . 'theme/' . $template['view_path']);
        $total = count($data);

        return $data;
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
        $table->show('name', '名称');
        $table->raw('path', '路径')->to('<a target="_blank" href="/admin/cmstemplatestatic/edit?path={fpath}&ext={ext}">{val}</a>');
        $table->match('type', '类型')->options(['js' => '脚本', 'css' => '样式', 'dir' => '目录'])->mapClassGroup([['js', 'success'], ['css', 'info']]);
        $table->show('ext', '后缀');
        $table->show('size', '大小')->to('{val}kb');
        $table->show('filectime', '创建时间')->getWrapper()->addStyle('width:180px');
        $table->show('filemtime', '编辑时间')->getWrapper()->addStyle('width:180px');

        $table->sortable('size,name,path,ext,create_time,update_time');

        $table->getToolbar()
            ->btnAdd(url('add', ['template_id' => input('template_id/d')]))
            ->btnRefresh()
            ->btnToggleSearch()
            ->btnLink(url('copyStatic', ['template_id' => input('template_id/d')]), '发布静态资源', 'btn-danger', 'mdi-content-copy');

        $table->getActionbar()
            ->btnEdit(url('edit', ['path' => '__data.fpath__', 'ext' => '__data.ext__']), '代码', 'btn-warning', 'mdi-table-edit', 'target="_blank"')
            ->btnDelete()
            ->mapClass([
                'edit' => ['hidden' => '__hi_edit__'],
                'delete' => ['hidden' => '__hi_delete__'],
            ]);;

        foreach ($data as &$d) {
            $d['fpath'] = str_replace(['/', '\\'], '--ds--', $d['path']);
        }

        $list = [];

        foreach ($data as &$d) {
            $dirs = explode('/', $d['path']);

            if (count($dirs) > 4) {
                $dir = $dirs[3];
                if (!isset($list[$dir])) {
                    $list[$dir] = [
                        'dir' => $dir . '/',
                        '__hi_edit__' => 1,
                        '__hi_delete__' => 1,
                        'is_default' => 1,
                        'size' => '--',
                        'type' => 'dir',
                    ];
                }
                $d['dir'] = '<span style="margin-left:10px"></span>-|--';
            } else {
                $d['dir'] = '--' . $dirs[3];
            }

            $list[] = $d;
        }

        $data = $list;
    }

    /**
     * 发布静态资源
     * @return mixed
     */
    public function copyStatic()
    {
        $template_id = input('template_id/d');
        $template = CmsTemplate::where('id', $template_id)->find();
        if (!$template) {
            $this->error('模板不存在');
        }

        $render = new Render();
        $res = $render->copyStatic($template);

        return $this->builder()->layer()->close($res['code'], $res['msg']);
    }


    public function edit()
    {
        $path = input('path');
        $ext = input('ext');

        if (request()->isGet()) {

            $builder = $this->builder($this->pageTitle, '编辑' . $ext, 'edit');

            $form = $builder->form();
            $this->form = $form;
            $this->isEdit = 1;

            $path = str_replace(['\\', '/', '--ds--'], DIRECTORY_SEPARATOR, $path);

            $view_path = App::getRootPath() . $path;

            $form->show('path', ' ')->showLabel(false)->size(12, 12)->value('路径：' . $path);
            $form->aceEditor('content', ' ')->setMode($ext == 'css' ? 'css' : 'javascript')->showLabel(false)->size(12, 12)->value(htmlentities(file_get_contents($view_path)));

            $form->butonsSizeClass('btn-md');
            $form->btnSubmit('提&nbsp;&nbsp;交', '12');

            $form->method('put');

            return $builder->render();
        }

        $this->checkToken();

        return $this->save($path);
    }

    public function add()
    {
        if (request()->isGet()) {

            $builder = $this->builder($this->pageTitle, $this->addText, 'add');
            $form = $builder->form();

            $this->form = $form;
            $this->isEdit = 0;

            $template_id = input('template_id/d');
            $template = CmsTemplate::where('id', $template_id)->find();
            $form->hidden('template_id')->value($template_id);
            $form->show('view_path', '模板基础路径')->value('theme/' . $template['view_path']);
            $form->radio('type', '资源类型')->default('css')->inline(false)->options(['css' => 'css样式：' . '/assest/css/css-xx.css', 'js' => 'js脚本：' . '/assest/js/js-xx.js']);
            $form->text('name', '文件名称')->required()->default('new-xx')->help('格式：只能包含英文数字-_，不支持多级目录')->afterSymbol('.js/.css');
            $form->method('post');

            return $builder->render();
        }

        $this->checkToken();

        return $this->save();
    }

    public function delete()
    {
        $this->checkToken();

        $ids = input('post.ids', '');
        $ids = array_filter(explode(',', $ids), 'strlen');
        if (empty($ids)) {
            $this->error('参数有误');
        }
        $res = 0;
        foreach ($ids as $id) {
            $file = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $id);

            if (is_file($file)) {
                @copy($file, $file .  date('YmdHis') . '.del');
                @unlink($file);

                $res += 1;
            }
        }

        if ($res) {
            $this->success('成功删除' . $res . '条数据');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 保存数据
     *
     * @param string $path
     * @return mixed
     */
    protected function save($path = '')
    {
        $data = request()->only([
            'content',
            'name',
            'template_id',
            'type',
        ], 'post');

        $result = $this->validate($data, []);

        if (true !== $result) {

            $this->error($result);
        }

        if (isset($data['template_id'])) //新键页面
        {
            $data['name'] = str_replace(['.js', '.css'], '', $data['name']);

            $template = CmsTemplate::where('id', $data['template_id'])->find();

            $templatePath = str_replace(['\\', '/'], '/', $template['view_path']);

            $dir = $data['type'] . '/';

            $path = 'theme/' . $templatePath . '/static/' . $dir . $data['name'] . '.' . $data['type'];

            if ($data['type'] == 'css') {
                $newTpl = '/*网站样式*/' . PHP_EOL . '@charset "UTF-8";' . PHP_EOL;
            } else {
                $newTpl = '/*网站js*/' . PHP_EOL;
            }

            $newFilePath = App::getRootPath() . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

            if (!is_file($newFilePath)) {
                $newRes = file_put_contents($newFilePath, $newTpl);
                if (!$newRes) {
                    $this->error('创建新文件失败');
                }
            }

            return $this->builder()->layer()->closeRefresh();
        }

        $file_path = App::getRootPath() . str_replace(['\\', '/', '--ds--'], DIRECTORY_SEPARATOR, $path);

        $res = file_put_contents($file_path, $data['content']);

        if (!$res) {
            $this->error('保存失败');
        }

        $this->success('保存成功，页面即将刷新~', null, '', 1);
    }
}
