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
use tpext\common\Tool;
use tpext\cms\common\Render;;

use tpext\cms\common\model;
use tpext\common\ExtLoader;
use tpext\cms\common\TemplaBuilder;

class MakeTemplate
{
    public function watch()
    {
        //模板主体
        ExtLoader::watch('cms_template_on_after_insert', function ($data) {
            $this->templateNew($data);
        });
        ExtLoader::watch('cms_template_on_before_update', function ($data) {
            $this->templateBeforeUpdate($data);
        });
        ExtLoader::watch('cms_template_on_after_update', function ($data) {
            $this->templateAfterUpdate($data);
        });

        ExtLoader::watch('cms_template_on_after_delete', function ($data) {
            $this->templateNew($data);
        });
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

        trace('模板[' . $data['name'] . ']新建，完成初始化', 'info');
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function templateBeforeUpdate($data)
    {
        if (!empty($data['id']) && !empty($data['view_path'])) {
            $view_path = App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $data['view_path'];
            $old = model\CmsTemplate::find($data['id']);
            //如果改变模板路径，迁移模板文件和静态资源
            if ($old['view_path'] != $data['view_path'] && !is_dir($view_path)) {
                Tool::copyDir(App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $old['view_path'], $view_path);
                $render = new Render();
                $render->copyStatic(['view_path' => $data['view_path']]);

                trace('模板[' . $data['name'] . ']路径修改，迁移静态资源：theme/' . $old['view_path'] . ' => theme/' . $old['view_path'], 'info');
            }
        }
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function templateAfterUpdate($data)
    {
        if (!empty($data['id']) && !empty($data['view_path'])) {
            //处理模板路径修改
            $htmls = model\CmsTemplateHtml::where(['template_id' => $data['id']])->select();
            foreach ($htmls as $html) {
                if (preg_match('/theme\/(\w+)\//i', $html['path'], $mchs)) {
                    if ($mchs[1] != $data['view_path']) {
                        $newPath = str_replace('theme/' . $mchs[1], 'theme/' . $data['view_path'], $html['path']);
                        trace('模板[' . $data['name'] . ']下的文件：' . $html['path'] . '路径不匹配，迁移到：' . $newPath, 'info');
                        $html->save(['path' => $newPath]);
                    }
                }
            }
        }

        trace('模板[' . $data['name'] . ']修改', 'info');
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function templateDel($data)
    {
        trace('模板[' . $data['name'] . ']删除', 'info');
    }
}
