<?php

namespace tpext\cms\admin\controller;

use think\Controller;
use tpext\cms\common\Cache;
use tpext\builder\traits\actions;
use tpext\cms\common\taglib\Processer;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsContentPage;
use tpext\cms\common\model\CmsContentModel;
use tpext\cms\common\model\CmsTemplateHtml;
use tpext\cms\common\model\CmsChannel as ChannelModel;

/**
 * Undocumented class
 * @title 栏目管理
 */
class Cmschannel extends Controller
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
     * @var ChannelModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new ChannelModel;

        $this->pageTitle = '栏目管理';
        $this->sortOrder = 'id desc';
        $this->pagesize = 8;

        $this->selectSearch = 'name';
        $this->selectFields = 'id,name,full_name';
        $this->selectTextField = 'name';

        $this->indexWith = ['bindhtmls'];
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
        $table->raw('__text__', '结构')->to('<a href="{preview_url}" target="_blank">{val}</a>')->getWrapper()->addStyle('text-align:left;');
        $table->image('logo', '封面图')->thumbSize(50, 50);
        $table->show('link', '链接')->default('暂无');
        $table->text('name', '名称')->autoPost('', true);
        $table->switchBtn('is_navi', '顶部导航')->autoPost();
        $table->switchBtn('is_show', '显示')->autoPost();
        $table->match('type', '类型')->options([1 => '不限', 2 => '目录', 3 => '分类'])->mapClassGroup([[1, 'success'], [2, 'info'], [3, 'warning']])->getWrapper()->addStyle('width:80px');
        $table->matches('model_ids', '内容模型')->optionsData(CmsContentModel::select(), 'name');
        $table->text('sort', '排序')->autoPost('', true)->getWrapper()->addStyle('width:60px');
        $table->text('pagesize', '分页大小')->autoPost()->getWrapper()->addStyle('width:60px');
        $table->raw('html_namess', '模板绑定');
        $table->show('order_by', '内容排序方式');
        $table->show('content_count', '内容统计');
        $table->fields('page_path', '生成路径')->with(
            $table->show('channel_path', '栏目生成路径')->to(function ($val, $row) {
                return 'c/' . str_replace('[id]', $row['id'], $val) . '.html';
            }),
            $table->show('content_path', '内容生成路径')->to('d/{val}.html')
        );
        $table->fields('create_time', '添加/修改时间')->with(
            $table->show('create_time', '添加时间'),
            $table->show('update_time', '修改时间'),
        );

        $table->getToolbar()
            ->btnAdd()
            ->btnOpenChecked(url('/admin/cmstemplatemake/makeChannel'), '生成静态', 'btn-yellow', 'mdi-xml', 'title="批量生成静态"')
            ->btnLink(url('refresh'), '刷新层级', 'btn-success', 'mdi-autorenew', 'data-layer-size="300px,150px" title="重新整理栏目上下级关系"')
            ->btnRefresh();

        $table->getActionbar()
            ->btnLink('add', url('add', ['parend_id' => '__data.pk__']), '', 'btn-secondary', 'mdi-plus', 'title="添加下级"')
            ->btnEdit()
            ->btnView()
            ->br()
            ->btnLink('make_channel', url('/admin/cmstemplatemake/makeChannel', ['ids' => '__data.pk__']), '', 'btn-yellow', 'mdi-xml', 'title="生成静态"')
            ->btnDelete()
            ->mapClass([
                'add' => [
                    'hidden' => '__hi_add__'
                ]
            ]);

        $template = CmsTemplate::where('id', 1)->find();
        Processer::setPath($template['prefix']);

        foreach ($data as &$d) {
            $d['__hi_add__'] = $d['type'] == 3;
            $d['preview_url'] = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $template['prefix']) . Processer::resolveChannelPath($d) . '.html';
            $htmlIds = [];
            foreach ($d['bindhtmls'] as $html) {
                $htmlIds[] = $html['html_id'];
            }
            $htmls = CmsTemplateHtml::where('id', 'in', $htmlIds)->order('type')->field('path,type')->select();
            $names = [];
            foreach ($htmls as $html) {
                $names[] = ($html['type'] == 'channel' ? '栏目:' : '详情:') . pathinfo($html['path'], PATHINFO_BASENAME);
            }
            $d['html_namess'] = implode('<br>', $names);
        }
    }

    /**
     * @title 刷新层级关系
     * @return mixed
     */
    public function refresh()
    {
        $builder = $this->builder('刷新');
        $step = input('step', '0');
        if ($step == 0) {
            Cache::deleteTag('cms_channel');
        }
        $list = [];
        $maxLevel = $this->dataModel->max('deep') + 1;
        if ($step <= $maxLevel) {
            $list = $this->dataModel->where('deep', $step)->select();
            foreach ($list as $li) {
                $upNodes = $this->dataModel->getUpperNodes($li);
                $names = [];
                $ids = [];
                foreach ($upNodes as $node) {
                    $names[] = $node['name'];
                    $ids[] = $node['id'];
                }
                $data = [];
                $data['full_name'] = implode(' / ', array_reverse($names));
                $data['path'] = implode(',', array_reverse($ids));
                $data['deep'] = count($ids);
                $li->save($data);
            }
            $next = $step + 1;
            $url = url('refresh', ['step' => $next]);
            $builder->display('<h4>（{$step}/{$maxLevel}）栏目层级已处理</h4><script>setTimeout(function(){location.href=\'{$url}\'},1000)</script>', ['step' => $step, 'maxLevel' => $maxLevel, 'url' => $url]);
        } else {
            return $builder->layer()->close(1, '栏目层级刷新完成');
        }

        return $builder;
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

        $tree = [0 => '根栏目'];
        $tree += $this->dataModel->getOptionsData($isEdit ? $data['id'] : 0); //数组合并不要用 array_merge , 会重排数组键 ，作为options导致bug

        $form->tab('基本信息');
        $form->text('name', '名称')->required();
        $form->select('parent_id', '上级')->required()->options($tree)->default(input('parend_id'));
        $form->image('logo', '封面图');
        $form->radio('type', '类型')->default(1)->options([1 => '不限', 2 => '目录', 3 => '分类'])
            ->required()->help('目录有下级，不能存文章。分类无下级，只能存文章')->blockStyle();
        $form->checkbox('model_ids', '内容模型')->optionsData(CmsContentModel::select(), 'name')
            ->default(1)->required()->help('可选多个模型，此栏目下只能存属于这些模型的内容')->blockStyle()->checkallBtn();
        $form->switchBtn('is_navi', '顶部导航')->default(1);
        $form->switchBtn('is_show', '显示')->default(1);
        $form->number('sort', '排序')->default(0)->required();

        if ($isEdit) {
            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }

        $list = $this->dataModel->order('sort')->select();

        $form->tab('生成设置');
        $form->number('pagesize', '分页大小')->default(12)->required();
        $form->text('order_by', '内容排序方式')->help('默认为：sort desc,publish_time desc,id desc');
        $form->text('channel_path', '栏目生成路径')->required()->size(2, 6)->beforSymbol('c/')->afterSymbol('[-page].html')->default('[id]')->help('[id]为栏目编号变量。如不生成，填入#');
        $form->text('content_path', '内容生成路径')->required()->size(2, 6)->beforSymbol('d/')->afterSymbol('.html')->default('[id]')->help('[id]为内容编号变量');
        $form->text('link', '跳转链接')->help('设置后覆盖栏目生成地址，用于外链或站内跳转');
        $form->selectTree('extend_ids', '附加栏目')->optionsData($list, 'name', 'id', 'parent_id', '')->help('可选多个附加栏目，此栏目列表页同时显示其他栏目内容');

        if ($isEdit) {
            $form->hidden('id');
            $form->tab('模板绑定');
            $form->raw('template_tips', '提示')->value('<p>每个栏目可绑定栏目列表模板和内容详情模板，若未绑定，则使用`default.html`</p>');

            $templates = CmsTemplate::select();
            $pages = CmsContentPage::where('html_type', 'in', ['channel', 'content'])
                ->where('to_id', $data['id'])
                ->select();

            $channel_template = [];
            $content_template = [];

            foreach ($templates as $tpl) {
                $tpl['name'] = ucfirst($tpl['name']);
                $channel_template[$tpl['id']] = 0;
                $content_template[$tpl['id']] = 0;

                foreach ($pages as $page) {
                    if ($page['template_id'] == $tpl['id']) {
                        if ($page['html_type'] == 'channel') {
                            $channel_template[$tpl['id']] = $page['html_id'];
                        } else {
                            $content_template[$tpl['id']] = $page['html_id'];
                        }
                    }
                }

                $form->divider($tpl['name'], '模板');
                $form->select('channel_template.' . $tpl['id'], '栏目列表')
                    ->beforOptions([0 => '默认'])
                    ->dataUrl(url('/admin/cmstemplatehtml/selectByTplid', ['template_id' => $tpl['id'], 'type' => 'channel']));

                $form->select('content_template.' . $tpl['id'], '内容详情')
                    ->beforOptions([0 => '默认'])
                    ->dataUrl(url('/admin/cmstemplatehtml/selectByTplid', ['template_id' => $tpl['id'], 'type' => 'content']));
                $form->raw('template_' . $tpl['id'], $tpl['name'])
                    ->to('<a class="label label-secondary" data-title="[' . $tpl['name'] . ']文件管理" onclick="top.$.fn.multitabs().create(this, true); return false;" href="/admin/cmstemplatehtml/index?template_id=' . $tpl['id'] . '">[管理<i title="打开文件管理页面" class="mdi mdi-arrow-top-right"></i>]</a>');
            }

            $data['channel_template'] = $channel_template;
            $data['content_template'] = $content_template;
        }

        $form->tab('SEO设置');
        $form->text('keywords', '关键字')->maxlength(255)->help('多个关键字请用英文逗号隔开');
        $form->textarea('description', '摘要')->maxlength(255)->help('留空则自动从内容中提取');
    }

    protected function _autopost()
    {
        $this->checkToken();

        $id = input('post.id/d', '');
        $name = input('post.name', '');
        $value = input('post.value', '');

        if (empty($id) || empty($name)) {
            $this->error(__blang('bilder_parameter_error'));
        }

        if (!empty($this->postAllowFields) && !in_array($name, $this->postAllowFields)) {
            $this->error(__blang('bilder_field_not_allowed'));
        }

        $info = $this->dataModel->where($this->getPk(), $id)->find();

        $res = $info && $info->save([$name => $value]);

        if ($res) {
            $this->success(__blang('bilder_update_succeeded'));
        } else {
            $this->error(__blang('bilder_update_failed_or_no_changes'));
        }
    }

    protected function save($id = 0)
    {
        $data = request()->only([
            'id',
            'name',
            'parent_id',
            'model_ids',
            'logo',
            'pagesize',
            'link',
            'is_show',
            'type',
            'sort',
            'channel_path',
            'content_path',
            'extend_ids',
            'order_by',
            'keywords',
            'description',
            'channel_template',
            'content_template',
        ], 'post');

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'sort|排序' => 'require|number',
            'type|类型' => 'require|number',
            'parent_id|上级' => 'require|number',
            'is_show|是否显示' => 'require',
            'channel_path|栏目生成路径' => 'require',
            'content_path|内容生成路径' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($data['parent_id']) {
            $parent = $this->dataModel->find($data['parent_id']);
            if ($parent && $parent['type'] == 3) {
                $this->error($parent['name'] . '不允许有下级栏目，请重新选择');
            }
        }
        $data['channel_path'] = str_replace('\\', '/', $data['channel_path']);
        $data['content_path'] = str_replace('\\', '/', $data['content_path']);

        if ($id && $data['parent_id'] == $id) {
            $this->error('上级不能是自己');
        }

        if (
            !empty($data['content_path']) && stripos($data['channel_path'], '[id]') === false
            && $exist = $this->dataModel->where(['channel_path' => $data['channel_path']])->find()
        ) {
            if ($id) {
                if ($exist['id'] != $id) {
                    $this->error('栏目生成路径，已被其他栏目占用-' . $exist['name']);
                }
            } else {
                $this->error('栏目生成路径，已被其他栏目占用-' . $exist['name']);
            }
        }

        if (!empty($data['content_path']) && stristr($data['content_path'], '[id]') === false) {
            $this->error('内容生成路径必须包含[id]变量');
        }

        if ($id) {
            $this->htmlBind($id, $data);
        }

        return $this->doSave($data, $id);
    }

    protected function htmlBind($id, $data)
    {
        $channel_template = $data['channel_template'] ?? [];
        $content_template = $data['content_template'] ?? [];

        foreach ($channel_template as $template_id => $html_id) {
            $exist = CmsContentPage::where('template_id', $template_id)
                ->where('html_type', 'channel')
                ->where('to_id', $id)
                ->find();
            if ($html_id > 0) {
                $tpl = CmsTemplateHtml::find($html_id);
                if (!$tpl) {
                    $this->error('模板不存在');
                }
                if ($tpl['is_default']) {
                    if ($exist) {
                        $exist->delete();
                    }
                    continue;
                }
                if ($exist) {
                    $exist->save([
                        'html_id' => $html_id,
                    ]);
                } else {
                    $perm = new CmsContentPage;
                    $perm->save([
                        'to_id' => $id,
                        'template_id' => $template_id,
                        'html_id' => $html_id,
                        'html_type' => 'channel',
                    ]);
                }
            } else {
                if ($exist) {
                    $exist->delete();
                }
            }
        }

        foreach ($content_template as $template_id => $html_id) {

            $exist = CmsContentPage::where('template_id', $template_id)
                ->where('html_type', 'content')
                ->where('to_id', $id)
                ->find();
            if ($html_id > 0) {
                $tpl = CmsTemplateHtml::find($html_id);
                if (!$tpl) {
                    $this->error('模板不存在');
                }
                if ($tpl['is_default']) {
                    if ($exist) {
                        $exist->delete();
                    }
                    continue;
                }
                if ($exist) {
                    $exist->save([
                        'html_id' => $html_id,
                    ]);
                } else {
                    $perm = new CmsContentPage;
                    $perm->save([
                        'to_id' => $id,
                        'template_id' => $template_id,
                        'html_id' => $html_id,
                        'html_type' => 'content',
                    ]);
                }
            } else {
                if ($exist) {
                    $exist->delete();
                }
            }
        }
    }
}
