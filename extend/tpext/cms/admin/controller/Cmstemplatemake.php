<?php

namespace tpext\cms\admin\controller;

use tpext\think\App;
use think\Controller;
use tpext\builder\traits\actions;
use tpext\cms\common\TemplaBuilder;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsChannel;
use tpext\cms\common\model\CmsTemplateHtml;
use tpext\cms\common\taglib\Processer;

/**
 * Undocumented class
 * @title 页面生成
 */
class Cmstemplatemake extends Controller
{
    use actions\HasBase;
    use actions\HasAdd;
    use actions\HasIndex;

    protected function initialize()
    {
        $this->pageTitle = '页面生成';
        $this->pagesize = 6;
        $this->pk = 'path';

        $this->pagesize = 9999;
    }

    /**
     * @title 模板生成
     * @return mixed
     */
    public function make()
    {
        $template_id = input('template_id/d');
        $template = CmsTemplate::where('id', $template_id)->find();
        if (!$template) {
            return $this->builder()->layer()->closeRefresh(0, '模板不存在-id:' . $template_id);
        }
        $builder = $this->builder('页面生成', '模板：' . $template['name']);
        $types = input('types', '');
        if (empty($types)) {
            CmsTemplateHtml::scanPageFiles($template_id, App::getRootPath() . 'theme/' . $template['view_path']);
            $form = $builder->form();
            $form->checkbox('types', '生成类型')
                ->options(['static' => '发布静态资源', 'channel' => '栏目静态', 'content' => '内容静态', 'index' => '首页静态'])
                ->inline(false)
                ->checkallBtn()
                ->required()
                ->default(['channel', 'content', 'index', 'route']);

            $form->ajax(false);
            $form->bottomButtons(false);
            $form->btnSubmit('生&nbsp;&nbsp;成', '12 col-lg-12 col-sm-12 col-xs-12');
        } else {
            Processer::setIsAdmin(true);
            $templateBuilder = new TemplaBuilder;
            if (is_string($types)) {
                $types = explode(',', $types);
            }
            $msgArr = [];
            if (in_array('static', $types)) {
                $resD = $templateBuilder->copyStatic($template);
                $msgArr[] = $resD['msg'];
                $types = array_filter($types, function ($item) {
                    return $item != 'static';
                });
            } else {
                $msgArr[] = '[静态资源]未选择发布';
            }

            $res = $templateBuilder->make(
                $template_id,
                [],
                $types,
                input('from_channel_id/d', 0),
                input('from_content_id/d', 0),
                input('content_done/d', 0),
                input('start_time/d', time())
            );

            if ($res['code'] == 1) {
                $msgArr = array_merge($msgArr, $res['msg_arr'] ?? []);
                $res['msg'] = '<div><label class="label-default">' . implode('</label></div><div><label class="label-default">', $msgArr) . '</label></div>';

                if ($res['is_over']) {
                    $builder->content()->display('{$msg|raw}', $res);
                } else {
                    $params = [
                        'template_id' => $template_id,
                        'types' => implode(',', $types),
                        'from_channel_id' => $res['from_channel_id'] ?: 0,
                        'from_content_id' => $res['from_content_id'] ?: 0,
                        'content_done' => $res['content_done'] ?: 0,
                        'start_time' => $res['start_time'] ?: time(),
                    ];
                    $res['url'] = url('make', $params);
                    $builder->content()->display('{$msg|raw}<div class="hidden" id="goon">若页面长时间未刷新，可点此<a href="{$url|raw}">继续</a></div><script>setTimeout(function(){location.href="{$url|raw}"},1000);setTimeout(function(){$("#goon").removeClass("hidden")},20000);</script>', $res);
                }
            } else {
                $builder->content()->display('{$msg}', $res);
            }
        }

        return $builder;
    }

    /**
     * @title 栏目生成
     * @return mixed
     */
    public function makeChannel()
    {
        $ids = explode(',', input('ids'));
        $channel_id = input('channel_id/d');

        if (!$channel_id) {
            $channel_id = array_shift($ids);
        }

        $channel = CmsChannel::where('id', $channel_id)->find();
        $types = input('types', '');
        if (empty($types)) {
            $builder = $this->builder('页面生成', '栏目：' . $channel['name']);
            $form = $builder->form();
            $form->select('template_id', '模板')->dataUrl('/admin/cmstemplate/selectpage')->default(1)->required();
            $form->checkbox('types', '生成类型')
                ->options(['channel' => '栏目静态', 'content' => '内容静态'])
                ->inline(false)
                ->required()
                ->default(['channel', 'content']);

            $form->ajax(false);
            $form->bottomButtons(false);
            $form->btnSubmit('生&nbsp;&nbsp;成', '12 col-lg-12 col-sm-12 col-xs-12');
        } else {
            $template_id = input('template_id/d');
            $template = CmsTemplate::where('id', $template_id)->find();
            if (!$template) {
                return $this->builder()->layer()->closeRefresh(0, '模板不存在-id:' . $template_id);
            }

            $builder = $this->builder('页面生成', '栏目：' . $channel['name'] . '，模板：' . $template['name']);
            Processer::setIsAdmin(true);
            $templateBuilder = new TemplaBuilder;
            if (is_string($types)) {
                $types = explode(',', $types);
            }

            $res = $templateBuilder->make(
                $template_id,
                [$channel_id],
                $types,
                0,
                input('from_content_id/d', 0),
                input('content_done/d', 0),
                input('start_time/d', time())
            );

            if ($res['code'] == 1) {
                $msgArr = $res['msg_arr'] ?? [];
                $res['msg'] = '<div><label class="label-default">' . implode('</label></div><div><label class="label-default">', $msgArr) . '</label></div>';

                $is_over = false;
                if ($res['is_over']) {
                    if (count($ids) > 0) {
                        $channel_id = array_shift($ids);
                        if ($channel_id) {
                            $res['from_content_id'] = 0;
                            $res['content_done'] = 0;
                        } else {
                            $is_over = true;
                        }
                    } else {
                        $is_over = true;
                    }
                }
                $params = [
                    'template_id' => $template_id,
                    'types' => implode(',', $types),
                    'channel_id' => $channel_id,
                    'ids' => implode(',', $ids),
                    'from_channel_id' => 0,
                    'from_content_id' => $res['from_content_id'] ?: 0,
                    'content_done' => $res['content_done'] ?: 0,
                    'start_time' => $res['start_time'] ?: time(),
                ];
                if ($is_over) {
                    $builder->content()->display('{$msg|raw}', $res);
                } else {
                    $res['url'] = url('makeChannel', $params);
                    $builder->content()->display('{$msg|raw}<div class="hidden" id="goon">若页面长时间未刷新，可点此<a href="{$url|raw}">继续</a></div><script>setTimeout(function(){location.href="{$url|raw}"},1000);setTimeout(function(){$("#goon").removeClass("hidden")},20000);</script>', $res);
                }
            } else {
                $builder->content()->display('{$msg}', $res);
            }
        }

        return $builder;
    }
}
