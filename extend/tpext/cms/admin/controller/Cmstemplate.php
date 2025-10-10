<?php

namespace tpext\cms\admin\controller;

use tpext\think\App;
use think\Controller;
use tpext\common\Tool;
use tpext\cms\common\Cache;
use tpext\builder\traits\actions;
use tpext\cms\common\RouteBuilder;
use tpext\cms\common\TemplaBuilder;
use tpext\cms\common\model\CmsTemplate as TemplateModel;

/**
 * Undocumented class
 * @title 内容模板
 */
class Cmstemplate extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;
    use actions\HasEdit;
    use actions\HasView;
    use actions\HasDelete;
    use actions\HasAutopost;

    /**
     * Undocumented variable
     *
     * @var TemplateModel
     */
    protected $dataModel;

    protected static $platforms = ['pc' => 'pc端', 'mobile' => '移动端'];

    protected function initialize()
    {
        $this->dataModel = new TemplateModel;
        $this->pageTitle = '内容模板';
        $this->enableField = 'is_open';
        $this->pagesize = 6;
        $this->sortOrder = 'sort';

        $this->selectSearch = 'name';
        $this->selectFields = 'id,name';
        $this->selectTextField = 'name';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['name'])) {
            $where[] = ['name', 'name', '%' . $searchData['name'] . '%'];
        }
        if (!empty($searchData['platform'])) {
            $where[] = ['platform', '=', $searchData['platform']];
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
        $search->select('platform', '类型')->options(self::$platforms);
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
        $table->show('name', '标题');
        $table->match('platform', '类型')->options(self::$platforms)->mapClassGroup([['pc', 'info'], ['mobile', 'success']]);
        $table->raw('view_path', '模板路径')->to('<code>theme/{val}</code>');
        $table->raw('prefix', '生成路径前缀')->to('<a href="{val}" target="_blank">{val}</a>');
        $table->show('description', '描述')->default('暂无');
        $table->show('pages_count', '页面数量');
        $table->raw('pages_manage', '模板页面')->to('<a class="label label-secondary" data-title="[{name}]文件管理" onclick="top.$.fn.multitabs().create(this, true); return false;" href="/admin/cmstemplatehtml/index?template_id={id}">[管理<i title="打开文件管理页面" class="mdi mdi-arrow-top-right"></i>]</a>');
        $table->raw('static_manage', '静态资源')->to('<a class="label label-secondary" data-title="[{name}]静态文件管理" onclick="top.$.fn.multitabs().create(this, true); return false;" href="/admin/cmstemplatestatic/index?template_id={id}">[管理<i title="打开静态文件管理页面" class="mdi mdi-arrow-top-right"></i>]</a>');

        $table->fields('times', '添加/修改时间')->with(
            $table->show('create_time', '添加时间'),
            $table->show('update_time', '修改时间')
        );

        $table->sortable('id,name,platform,sort,prefix,create_time,update_time');

        $table->getToolbar()
            ->btnAdd()
            ->btnRefresh()
            ->btnToggleSearch()
            ->btnLink(url('makeRoute'), '生成路由', 'btn-danger', 'mdi-link-variant')
            ->btnLink(url('clearTemp'), '清除页面缓存', 'btn-warning', 'mdi-delete-forever');

        $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnLink('make', url('/admin/cmstemplatemake/make', ['template_id' => '__data.pk__']), '生成', 'btn-success', 'mdi-cloud-braces ', 'data-layer-size="98%,98%"')
            ->btnDelete()->mapClass([
                'delete' => [
                    'disabled' => function ($data) {
                        return $data['id'] == 1;
                    }
                ]
            ]);
    }

    /**
     * @title 生成路由
     * @return mixed
     */
    public function makeRoute()
    {
        $routeBuilder = new RouteBuilder;
        $routeBuilder->builder(true);
        return $this->builder()->layer()->closeRefresh(1, '已生成路由route/tpex-tcms.php');
    }

    public function clearTemp()
    {
        $tags = ['cms_html', 'cms_page', 'cms_template', 'cms_channel', 'cms_content', 'cms_position', 'cms_banner', 'cms_tag'];

        $msgs = ['已清除数据缓存'];
        foreach ($tags as $tag) {
            Cache::deleteTag($tag);
        }

        Tool::deleteDir(App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'theme');

        $msgs[] = '已清模板除缓存目录runtime/temp/theme';

        $builder = new TemplaBuilder;
        $templates = $this->dataModel->select();
        foreach ($templates as $template) {
            $builder->clearHtml($template);
            $msgs[] = '已清除' . $template['prefix'] . '路径下的channel、content、index的html';
        }
        return $this->builder()->layer()->closeRefresh(1, implode('、', $msgs));
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

        $form->text('name', '标题')->required()->maxlength(55);
        $form->radio('platform', '类型')->required()->options(self::$platforms)->default('pc');
        $form->text('view_path', '模板路径')->beforSymbol('/theme/')->required()->help('模板放在网站根目录的/theme/目录下，如：pc-default。格式：只能包含[英文数字-_]，不支持多级目录')->default('your-path');
        $form->text('prefix', '路径前缀')->required()->maxlength(25)->default('/')->help('页面生成的访问基础路径，如/、/m/、/h5/');
        $form->textarea('description', '描述')->maxlength(255);
        $form->number('sort', '排序')->default(0);
        if ($isEdit) {
            $form->hidden('id');
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
            $form->divider('模板信息');

            $view_path = App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $data['view_path'];
            TemplateModel::initPath($view_path);

            $form->raw('index', '首页页模板')->value('<code>index.html</code>：' . (is_file($view_path . '/index.html') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
            $form->raw('channel', '栏目默认模板')->value('<code>/channel/default.html</code>：' . (is_file($view_path . '/channel/default.html') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
            $form->raw('content', '内容默认模板')->value('<code>/content/default.html</code>：' . (is_file($view_path . '/content/default.html') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
            $form->raw('common', '共用模板')->value('<code>/common/</code>：' . (is_dir($view_path . '/common') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
            $form->raw('dynamic', '动态解析模板')->value('<code>/dynamic/</code>：' . (is_dir($view_path . '/dynamic') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
            $form->raw('static', '静态资源目录')->value('<code>/static/</code>：' . (is_dir($view_path . '/static') ? '<label class="label label-success">存在</label>' : '<label class="label label-danger">不存在</label>'));
        }
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
            'name',
            'platform',
            'view_path',
            'prefix',
            'description',
            'sort',
        ], 'post');

        $result = $this->validate($data, [
            'name|标题' => 'require',
            'platform|平台类型' => 'require',
            'view_path|模板路径' => 'require',
            'prefix|路径前缀' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        $data['view_path'] = preg_replace('/[^\w\-]/', '', trim(strtolower($data['view_path']), '/\\'));
        if ($data['prefix'] == '') {
            $data['prefix'] = '/';
        } else if ($data['prefix'] !== '/') {
            $data['prefix'] = '/' . preg_replace('/[^\w\-]/', '', trim(strtolower($data['prefix']), '/\\')) . '/';
        }

        return $this->doSave($data, $id);
    }
}
