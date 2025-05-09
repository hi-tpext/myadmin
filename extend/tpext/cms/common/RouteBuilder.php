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

namespace tpext\cms\common;

use tpext\think\App;
use tpext\common\ExtLoader;
use tpext\cms\common\model\CmsChannel;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsTemplateHtml;

class RouteBuilder
{
    public function builder($forceWrite = false)
    {
        $channels = CmsChannel::select();

        $channelPathArr = [];
        $contentPathArr = [];

        foreach ($channels as $cha) {
            $channel_path_key = md5($cha['channel_path']);
            $content_path_key = md5($cha['content_path']);

            if (!isset($channelPathArr[$channel_path_key])) {
                $channelPathArr[$channel_path_key] = [
                    'ids' => [],
                    'path' => $cha['channel_path'],
                ];
            }
            if (!isset($contentPathArr[$content_path_key])) {
                $contentPathArr[$content_path_key] = [
                    'ids' => [],
                    'path' => $cha['content_path'],
                ];
            }

            $channelPathArr[$channel_path_key]['ids'][] = $cha['id'];
            $contentPathArr[$content_path_key]['ids'][] = $cha['id'];
        }

        $indexRoutes = $this->makeIndexRoute();
        $channelRoutes = $this->makeChannelRoute($channelPathArr);
        $contentRoutes = $this->makeContentRoute($contentPathArr);

        try {
            $this->witeToFile(array_merge($indexRoutes, $channelRoutes, $contentRoutes), $forceWrite);
        } catch (\Throwable $e) {
            trace('write route error:' . $e->__tostring(), 'error');
            return;
        }
    }

    /**
     * 生成首页路由
     * @return string[]
     */
    public function makeIndexRoute()
    {
        return [
            "Route::get('__prefix__$', Page::class . '@index')",
            "Route::get('__prefix__index$', Page::class . '@index')",
        ];
    }

    /**
     * 生成栏目路由
     * @param array $urlPaths
     * @return string[]
     */
    protected function makeChannelRoute($urlPaths)
    {
        $rules = [];
        foreach ($urlPaths as $url) {
            $path = $url['path'];
            if ($path == '#') {
                continue;
            }
            if (stristr($path, '[id]') === false) {
                $ids = $url['ids'];
                foreach ($ids as $id) {
                    $rules[] = [
                        'rule' => "Route::get('__prefix__channel/{$path}-<page>$', Page::class . '@channel')",
                        'append' => ['id' => $id],
                    ];
                    $rules[] = [
                        'rule' => "Route::get('__prefix__channel/{$path}$', Page::class . '@channel')",
                        'append' => ['id' => $id],
                    ];
                }
            } else {
                $path = str_replace('[id]', '<id>', $path);
                $rules[] = "Route::get('__prefix__channel/{$path}-<page>$', Page::class . '@channel')->pattern(['id' => '\d+', 'page' => '\d+'])";
                $rules[] = "Route::get('__prefix__channel/{$path}$', Page::class . '@channel')->pattern(['id' => '\d+'])";
            }
        }
        return $rules;
    }

    /**
     * 生成内容路由
     * @param array $urlPaths
     * @return string[]
     */
    protected function makeContentRoute($urlPaths)
    {
        $rules = [];
        foreach ($urlPaths as $url) {
            $path = $url['path'];
            $path = str_replace('[id]', '<id>', $path);
            $rules[] = "Route::get('__prefix__content/{$path}$', Page::class . '@content')->pattern(['id' => '\d+'])";
        }

        $rules[] = "Route::get('__prefix__content/__click__<id>$', Page::class . '@click')->pattern(['id' => '\d+'])->ajax()";

        return $rules;
    }

    /**
     * 获取单页列表
     * @param array|CmsTemplate $template
     * @return string[]
     */
    protected function getSinglePages($template)
    {
        $singlePages = CmsTemplateHtml::where('template_id', $template['id'])->where('type', 'single')->select();

        if (empty($singlePages)) {
            return [];
        }

        $pages = [];
        foreach ($singlePages as $page) {
            $path = preg_replace('/theme\/[\w\-]+?\/([\w\-]+?).html$/i', '$1', $page['path']);
            $pages[] = [
                'id' => $page['to_id'],
                'path' => $path,
            ];
        }

        return $pages;
    }

    /**
     * 获取动态页列表
     * @param array|CmsTemplate $template
     * @return string[]
     */
    protected function getDynamicPages($template)
    {
        $dynamicPages = CmsTemplateHtml::where('template_id', $template['id'])
            ->where('type', 'dynamic')->select();

        if (empty($dynamicPages)) {
            return [];
        }

        $pages = [];
        foreach ($dynamicPages as $page) {
            $path = preg_replace('/theme\/[\w\-]+?\/dynamic\/([\w\-]+?).html$/i', '$1', $page['path']);
            $pages[] = [
                'id' => $page['id'],
                'path' => $path,
            ];
        }

        return $pages;
    }

    /**
     * Undocumented function
     *
     * @param array $routesGroup
     * @param boolean $forceWrite
     * @return void
     */
    protected function witeToFile($routesRules, $forceWrite)
    {
        $routeFile = App::getRootPath() . 'route/tpext-cms.php';

        if (is_file($routeFile) && time() - filemtime($routeFile) < 120 && !$forceWrite) {
            return;
        }

        $templates = CmsTemplate::select();

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = '/**';
        $lines[] = ' *tpext.cms 自动生成扩展路由，请不要手动修改.';
        $lines[] = ' *时间:' . date('Y-m-d H:i:s');
        $lines[] = ' */';
        $lines[] = '';
        if (ExtLoader::isWebman()) {
            $lines[] = 'use tpext\cms\common\webman\Route;//仅用于cms路由，不要使用在其他地方';
        } else {
            $lines[] = 'use think\facade\Route;';
        }

        $lines[] = 'use tpext\cms\common\Page;';
        $lines[] = '';

        foreach ($templates as $tmpl) {
            $prefix = trim($tmpl['prefix'], '/');

            $singlePages = $this->getSinglePages($tmpl);
            $dynamicPages = $this->getDynamicPages($tmpl);

            if ($prefix) {
                foreach ($routesRules as $rule) {
                    if (is_array($rule)) {
                        $append = $rule['append'];
                        $rule = $rule['rule'];
                        $append['tpl_id'] = $tmpl['id'];
                        $rule = str_replace('__prefix__', '/' . $prefix . '/', $rule) . "->append([";
                        foreach ($append as $k => $v) {
                            $rule .= "'{$k}' => {$v}, ";
                        }
                        $rule = rtrim($rule, ', ') . "]);";
                        $lines[] = $rule;
                    } else {
                        $lines[] = str_replace('__prefix__', '/' . $prefix . '/', $rule) . "->append(['tpl_id' => {$tmpl['id']}]);";
                    }
                }

                foreach ($singlePages as $page) {
                    $lines[] = "Route::get('/{$prefix}/{$page['path']}$', Page::class . '@content')->append(['id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                }

                foreach ($dynamicPages as $page) {
                    if ($page['path'] == 'tag') {
                        $lines[] = "Route::get('/{$prefix}/dynamic/tag-<id>$', Page::class . '@dynamic')->pattern(['id' => '\d+'])->append(['html_id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                    } else {
                        $lines[] = "Route::get('/{$prefix}/dynamic/{$page['path']}$', Page::class . '@dynamic')->append(['html_id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                    }
                }
            } else {
                foreach ($routesRules as $rule) {
                    if (is_array($rule)) {
                        $append = $rule['append'];
                        $rule = $rule['rule'];
                        $append['tpl_id'] = $tmpl['id'];
                        $rule = str_replace('__prefix__', '/', $rule) . "->append([";
                        foreach ($append as $k => $v) {
                            $rule .= "'{$k}' => {$v}, ";
                        }
                        $rule = rtrim($rule, ', ') . "]);";
                        $lines[] = $rule;
                    } else {
                        $lines[] = str_replace('__prefix__', '/', $rule) . "->append(['tpl_id' => {$tmpl['id']}]);";
                    }
                }

                foreach ($singlePages as $page) {
                    $lines[] = "Route::get('/{$page['path']}$', Page::class . '@content')->append(['id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                }

                foreach ($dynamicPages as $page) {
                    if ($page['path'] == 'tag') {
                        $lines[] = "Route::get('/dynamic/tag-<id>$', Page::class . '@dynamic')->pattern(['id' => '\d+'])->append(['html_id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                    } else {
                        $lines[] = "Route::get('/dynamic/{$page['path']}$', Page::class . '@dynamic')->append(['html_id' => {$page['id']}, 'tpl_id' => {$tmpl['id']}]);";
                    }
                }
            }

            $lines[] = '';
        }

        if (!is_dir(App::getRootPath() . 'route/')) {
            mkdir(App::getRootPath() . 'route/', 0755, true);
        }

        file_put_contents($routeFile, implode(PHP_EOL, $lines));

        if (ExtLoader::isWebman()) {
            $routeConfig = file_get_contents(config_path() . '/route.php');

            if (!strstr($routeConfig, 'route/tpext-cms.php')) {
                $routeConfig .= PHP_EOL . '//引入cms路由';
                $routeConfig .= PHP_EOL . "if (file_exists(base_path('route/tpext-cms.php'))) {";
                $routeConfig .= PHP_EOL . "    require_once base_path('route/tpext-cms.php');";
                $routeConfig .= PHP_EOL . '}';
            }

            file_put_contents(config_path() . '/route.php', $routeConfig);
        }
    }
}
