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

use tpext\common\ExtLoader;
use tpext\cms\common\RouteBuilder;

class MakeRoute
{
    public function watch()
    {
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

        //模板主体修改时，重新生成路由
        ExtLoader::watch('cms_template_on_after_insert', function ($data) {
            $this->templateChange($data);
        });
        ExtLoader::watch('cms_template_on_after_update', function ($data) {
            $this->templateChange($data);
        });
        ExtLoader::watch('cms_template_on_after_delete', function ($data) {
            $this->templateChange($data);
        });

        //html修改时，重新生成路由
        ExtLoader::watch('cms_template_html_on_after_insert', function ($data) {
            $this->contentPageChange($data);
        });
        ExtLoader::watch('cms_template_html_on_after_update', function ($data) {
            $this->contentPageChange($data);
        });
        ExtLoader::watch('cms_template_html_on_after_delete', function ($data) {
            $this->contentPageChange($data);
        });
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function channelChange($data)
    {
        $routeBuilder = new RouteBuilder;
        $routeBuilder->builder(true);
        trace('栏目[' . $data['name'] . ']修改触路由生成', 'info');
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function templateChange($data)
    {
        $routeBuilder = new RouteBuilder;
        $routeBuilder->builder(true);
        trace('模板[' . $data['name'] . ']修改触路由生成', 'info');
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function contentPageChange($data)
    {
        $routeBuilder = new RouteBuilder;
        $routeBuilder->builder(true);
        trace('页面[' . $data['name'] . ']修改触路由生成', 'info');
    }
}
