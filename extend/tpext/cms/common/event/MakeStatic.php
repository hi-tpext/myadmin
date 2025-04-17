<?php
// +----------------------------------------------------------------------
// | tpext.cms
// +----------------------------------------------------------------------
// | Copyright (c) tpext.cms All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lhy <ichynul@163.com>
// +----------------------------------------------------------------------

namespace tpext\cms\common\event;

use tpext\think\App;
use tpext\cms\common\model;
use tpext\common\ExtLoader;
use tpext\cms\common\TemplaBuilder;
use tpext\cms\common\taglib\Processer;

class MakeStatic
{
    public function watch()
    {
        // 监听内容相关事件
        ExtLoader::watch('cms_content_on_after_insert', function ($data) {
            $this->contentChange($data);
        });
        ExtLoader::watch('cms_content_on_after_update', function ($data) {
            $this->contentChange($data);
        });
        ExtLoader::watch('cms_content_on_after_delete', function ($data) {
            $this->contentChange($data);
        });

        // 监听栏目相关事件
        ExtLoader::watch('cms_channel_on_after_insert', function ($data) {
            $this->channelChange($data);
        });
        ExtLoader::watch('cms_channel_on_after_update', function ($data) {
            $this->channelChange($data);
        });
        ExtLoader::watch('cms_channel_on_after_delete', function ($data) {
            $this->channelChange($data);
        });

        ExtLoader::watch('cms_template_on_after_insert', function ($data) {
            $this->templateNew($data);
        });
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function contentChange($data)
    {
        $buiilder = new TemplaBuilder;
        $templates = model\CmsTemplate::select();
        $channel = model\CmsChannel::withTrashed()->where('id', $data['channel_id'])->find();
        trace('文章[' . $data['title'] . ']修改触发静态生成', 'info');
        foreach ($templates as $template) {
            trace('处理模板：' . $template['name'], 'info');
            Processer::setPath($template['prefix']);
            $res = $buiilder->makeContent($template, $channel, $data);
            trace('[' . $data['title'] . ']生成静态文件：' . $res['msg'], 'info');
            $res = $buiilder->makeChannel($template, $channel);
            trace('[' . $channel['name'] . ']列表第一页生成静态文件：' . $res['msg'], 'info');
            $res = $buiilder->makeIndex($template);
            trace('[模板首页]成静态文件：' . $res['msg'], 'info');
        }
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function channelChange($data)
    {
        $buiilder = new TemplaBuilder;
        $templates = model\CmsTemplate::select();
        trace('栏目[' . $data['name'] . ']修改触发静态生成', 'info');
        foreach ($templates as $template) {
            trace('处理模板：' . $template['name'], 'info');
            Processer::setPath($template['prefix']);
            $res = $buiilder->makeChannel($template, $data);
            trace('[' . $data['name'] . ']列表第一页生成静态文件：' . $res['msg'], 'info');
            $res = $buiilder->makeIndex($template);
            trace('[模板首页]成静态文件：' . $res['msg'], 'info');
        }
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function templateNew($data)
    {
        $view_path = App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $data['view_path'];
        model\CmsTemplate::initPath($view_path);
        model\CmsTemplateHtml::scanPageFiles($data['id'], $view_path);
        $builer = new TemplaBuilder;
        $builer->copyStatic($data);

        trace('模板[' . $data['name'] . ']触路由生成', 'info');
    }
}
