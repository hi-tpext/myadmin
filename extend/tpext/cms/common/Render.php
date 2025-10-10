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
use tpext\common\Tool;
use tpext\cms\common\Cache;
use tpext\common\ExtLoader;
use tpext\cms\common\taglib\Table;
use tpext\cms\common\taglib\Processer;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsContentPage;
use tpext\cms\common\model\CmsTemplateHtml;

class Render
{
    /**
     * @var CmsContentPage
     */
    protected $pageModel = null;

    /**
     * @var CmsTemplateHtml
     */
    protected $htmlModel = null;

    protected $cacheTime = 3600 * 24 * 7;

    public function __construct()
    {
        $this->pageModel = new CmsContentPage();
        $this->htmlModel = new CmsTemplateHtml();
    }

    /**
     * 生成首页
     * @param array|CmsTemplate $template
     * @param int $page
     * @return array
     */
    public function index($template)
    {
        $tplHtml = $this->htmlModel->where('is_default', 1)
            ->where(['type' => 'index', 'template_id' => $template['id']])
            ->cache('cms_html_index_' . $template['id'], $this->cacheTime, 'cms_html')
            ->find();

        if (!$tplHtml) {
            return ['code' => 0, 'msg' => '[' . $template['name'] . ']无首页模板'];
        }

        try {
            $tplFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $tplHtml['path']);
            $vars = [
                'page_title' => '首页' . '_',
                '__site_home__' => $template['prefix'],
                '__page_type__' => 'index',
                '__set_order_by__' => 'is_recommend desc,',
                '__wconf__' => $this->getWconfig(),
            ];
            $vars['vars'] = $vars;
            $config = [
                'cache_prefix' => $tplHtml['path'],
                'tpl_replace_string' => ['@static@' => '/theme/' . $template['view_path'] . '/', '@site_home@' => $template['prefix']],
                'view_path' => App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $template['view_path'] . DIRECTORY_SEPARATOR,
                'tpl_cache' => true,
            ];
            $view = new View(App::getRootPath() . $tplFile, $vars, $config);
            $out = $view->getContent();
            $out = $this->replaceStaticPath($template, $out);

            $out = str_replace('</body>', '<script type="text/javascript">' . $this->advScript() . "\n" . '</script>' . "\n" . '</body>', $out);
            return ['code' => 1, 'msg' => 'ok', 'data' => $out];
        } catch (\Throwable $e) {
            trace($e->__toString());
            return ['code' => 0, 'msg' => '[首页]生成出错，' . str_replace(App::getRootPath(), '', $e->getFile() . '#' . $e->getLine()) . '|' . $e->getMessage()];
        }
    }

    /**
     * 生成栏目页
     * @param array|CmsTemplate $template
     * @param array|mixed $channel
     * @param int $page
     * @return array
     */
    public function channel($template, $channel, $page = 1)
    {
        $tplHtml = $this->getHtml($template, 'channel', $channel['id']); //获取绑定的模板

        if (!$tplHtml) {
            //无绑定，使用默认模板
            $tplHtml = $this->htmlModel->where('is_default', 1)
                ->where(['type' => 'channel', 'template_id' => $template['id']])
                ->cache('cms_html_channel_default_' . $template['id'], $this->cacheTime, 'cms_html')
                ->find();
        }

        if (!$tplHtml) {
            return ['code' => 0, 'msg' => '[' . $channel['name'] . ']栏目无绑定模板', 'is_over' => true];
        }

        try {
            $tplFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $tplHtml['path']);
            if ($channel['is_show'] != 1 || $channel['delete_time'] || $channel['channel_path'] == '#') {
                return ['code' => 0, 'msg' => '栏目不存在'];
            } else {
                $channel_ids = $channel['extend_ids'] ? explode(',', $channel['extend_ids']) : [];

                $vars = [
                    'page' => $page,
                    'id' => $channel['id'],
                    'channel_id' => $channel['id'],
                    'cid' => $channel['id'],
                    'parent_id' => $channel['parent_id'],
                    'pid' => $channel['parent_id'],
                    'channel_ids' => !empty($channel_ids) ? implode(',', array_merge([$channel['id']], $channel_ids)) : '',
                    'children_ids' => implode(',', $channel['children_ids'] ?? []),
                    'extend_ids' => $channel['extend_ids'],
                    'channel' => $channel,
                    'page_title' => $channel['name'] . '_',
                    'page_description' => $channel['description'] ?: $channel['name'],
                    'page_keywords' => $channel['keywords'] ?: $channel['name'],
                    '__site_home__' => $template['prefix'],
                    '__page_type__' => 'channel',
                    '__set_pagesize__' => $channel['pagesize'],
                    '__set_order_by__' => 'is_top desc,',
                    '__set_page_path__' => $template['prefix'] . Processer::resolveChannelPath($channel) . '-[PAGE].html',
                    '__wconf__' => $this->getWconfig(),
                ];
                $vars['vars'] = $vars;
                $config = [
                    'cache_prefix' => $tplHtml['path'],
                    'tpl_replace_string' => ['@static@' => '/theme/' . $template['view_path'] . '/', '@site_home@' => $template['prefix']],
                    'view_path' => App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $template['view_path'] . DIRECTORY_SEPARATOR,
                    'tpl_cache' => true,
                ];
                $view = new View(App::getRootPath() . $tplFile, $vars, $config);
                $out = $view->getContent();
                $out = $this->replaceStaticPath($template, $out);

                $out = str_replace('</body>', '<script type="text/javascript">' . $this->advScript() . "\n" . '</script>' . "\n" . '</body>', $out);
            }
            return ['code' => 1, 'msg' => 'ok', 'data' => $out];
        } catch (\Throwable $e) {
            trace($e->__toString());
            return ['code' => 0, 'msg' => '[' . $channel['name'] . ']栏目生成出错，' . str_replace(App::getRootPath(), '', $e->getFile() . '#' . $e->getLine() . '|' . $e->getMessage()) . '。模板文件：' . $tplFile];
        }
    }

    /**
     * 生成内容页
     * @param array|CmsTemplate $template
     * @param array|mixed $content
     * @param int $is_static
     * @return array
     */
    public function content($template, $content, $is_static = 0)
    {
        $tplHtml = $this->getHtml($template, 'single', $content['id']); //获取绑定的单页模板

        if (!$tplHtml) {
            $tplHtml = $this->getHtml($template, 'content', $content['channel_id']); //获取绑定的内容模板
        }

        if (!$tplHtml) {
            //无绑定，使用默认模板
            $tplHtml = $this->htmlModel->where('is_default', 1)
                ->where(['type' => 'content', 'template_id' => $template['id']])
                ->cache('cms_html_content_default_' . $template['id'], $this->cacheTime, 'cms_html')
                ->find();
        }

        if (!$tplHtml) {
            return ['code' => 0, 'msg' => '[' . $content['title'] . ']内容无绑定模板'];
        }

        try {
            $tplFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $tplHtml['path']);
            $out = '';
            if ($content['__not_found__'] || $content['is_show'] != 1 || $content['delete_time']) {
                return ['code' => 0, 'msg' => '内容不存在'];
            } else {
                if ($is_static == 1) {
                    $content['click'] = '<span id="__content_click__">--<span>';
                } else {
                    $content['click'] = $this->click($content['id']);
                }
                $vars = [
                    'id' => $content['id'],
                    'channel_id' => $content['channel_id'],
                    'cid' => $content['channel_id'],
                    'parent_id' => $content['channel']['parent_id'],
                    'pid' => $content['channel']['parent_id'],
                    'content_id' => $content['id'],
                    'content' => $content,
                    'channel' => $content['channel'],
                    'page_title' => $content['title'] . '_' . $content['channel']['name'] . '_',
                    'page_description' => $content['description'] ?: $content['title'],
                    'page_keywords' => $content['keywords'] ?: $content['title'],
                    '__site_home__' => $template['prefix'],
                    '__page_type__' => 'content',
                    '__wconf__' => $this->getWconfig(),
                ];
                $vars['vars'] = $vars;
                $config = [
                    'cache_prefix' => $tplHtml['path'],
                    'tpl_replace_string' => ['@static@' => '/theme/' . $template['view_path'] . '/', '@site_home@' => $template['prefix']],
                    'view_path' => App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $template['view_path'] . DIRECTORY_SEPARATOR,
                    'tpl_cache' => true,
                ];
                $view = new View(App::getRootPath() . $tplFile, $vars, $config);
                $out = $view->getContent();
                $out = $this->replaceStaticPath($template, $out);

                if ($is_static == 1) {
                    $url = $template['prefix'] . 'd/__click__' . $content['id'];
                    $out = str_replace('</body>', '<script type="text/javascript">' . $this->clickScript($url) . "\n" . '</script>' . "\n" . '</body>', $out);
                }

                $out = str_replace('</body>', '<script type="text/javascript">' . $this->advScript() . "\n" . '</script>' . "\n" . '</body>', $out);
            }
            return ['code' => 1, 'msg' => 'ok', 'data' => $out];
        } catch (\Throwable $e) {
            trace($e->__toString());
            return ['code' => 0, 'msg' => '[' . $content['title'] . ']内容生成出错，' . str_replace(App::getRootPath(), '', $e->getFile() . '#' . $e->getLine() . '|' . $e->getMessage()) . '。模板文件：' . $tplFile];
        }
    }

    /**
     * 内容点击
     * 
     * @param int $id
     * @return int
     */
    public function click($id)
    {
        $key = 'cms_content_click_' . $id;
        $table = 'cms_content';
        $dbNameSpace = Processer::getDbNamespace();
        $contentScope = Table::defaultScope($table);

        $click = 0;
        if (Cache::has($key)) {
            $click = Cache::get($key);
        } else {
            $content = $dbNameSpace::name($table)
                ->where('id', $id)
                ->where($contentScope)
                ->find();

            $click = $content ? $content['click'] : 0;
        }

        $click += 1;
        Cache::tag('cms_content')->set('cms_content_click_' . $id, $click);

        if ($click % 10 == 0) {
            $dbNameSpace::name($table)
                ->where('id', $id)
                ->where($contentScope)
                ->update(['click' => $click]);
        }

        ExtLoader::trigger('cms_content_click', $id);

        return $click;
    }

    protected function clickScript($url)
    {
        $script = <<<EOT

    var __content_click__ = document.getElementById("__content_click__");
    var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("");
    xhr.open("GET", "{$url}", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if(__content_click__) {
                __content_click__.innerText = xhr.responseText;
            }
        }
    };
    xhr.send();
EOT;

        return $script;
    }

    protected function advScript()
    {
        $script = <<<EOT

    try {
        if (window.console && window.console.log) {
            console.log("%c Powered by © tpext.cms", "font-size:32px;background:#333;color:#fff");
            console.log("%c https://github.com/hi-tpext/tpext-cms", "font-size:20px;background:#333;color:#fff");
        }
    } catch (e) {
     
    }
EOT;

        return $script;
    }

    /**
     * 生成动态页面
     * @param array|CmsTemplate $template
     * @param int $tplHtmlId
     * @return array
     */
    public function dynamic($template, $tplHtmlId)
    {
        $tplHtml = $this->htmlModel->where('id', $tplHtmlId)
            ->where('template_id', $template['id'])
            ->where('type', 'in', ['dynamic', 'single'])
            ->cache('cms_html_' . $tplHtmlId, $this->cacheTime, 'cms_html')
            ->find();

        if (!$tplHtml) {
            return ['code' => 0, 'msg' => '模板不存在-' . $template['id'] . '-' . $tplHtmlId];
        }

        try {
            $tplFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $tplHtml['path']);
            $page_path = '';
            $get = request()->get();
            if (!empty($get)) {
                unset($get['page']);
                $page_path = '?' . (empty($get) ? '' : http_build_query($get) . '&') . 'page=[PAGE]';
            }

            $vars = [
                '__site_home__' => $template['prefix'],
                '__wconf__' => $this->getWconfig(),
                '__set_page_path__' => $page_path,
                '__page_type__' => 'dynamic',
            ];

            $param = request()->param();
            array_walk($param, function (&$value, $key) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $value = $this->sqlGuard($value);
            });

            $vars = array_merge($vars, $param);
            $vars['vars'] = $vars;
            $config = [
                'cache_prefix' => $tplHtml['path'],
                'tpl_replace_string' => ['@static@' => '/theme/' . $template['view_path'] . '/', '@site_home@' => $template['prefix']],
                'view_path' => App::getRootPath() . 'theme' . DIRECTORY_SEPARATOR . $template['view_path'] . DIRECTORY_SEPARATOR,
                'tpl_cache' => true,
            ];
            $view = new View(App::getRootPath() . $tplFile, $vars, $config);
            $out = $view->getContent();
            $out = $this->replaceStaticPath($template, $out);
            return ['code' => 1, 'msg' => 'ok', 'data' => $out];
        } catch (\Throwable $e) {
            trace($e->__toString());
            return ['code' => 0, 'msg' => '[页面]生成出错，' . str_replace(App::getRootPath(), '', $e->getFile() . '#' . $e->getLine()) . '|' . $e->getMessage()];
        }
    }

    /**
     * sql 注入防御
     * @param string $val
     * @return string
     */
    protected function sqlGuard($val)
    {
        $val = strip_tags($val);

        if (preg_match('/\b(?:select|delete)\b.+?\bfrom\b/is', $val)) {
            return 'invalid words';
        }

        if (preg_match('/\bunion\b.+?\bselect\b/is', $val)) {
            return 'invalid words';
        }

        return $val;
    }

    /**
     * @param array|CmsTemplate $template
     * @param string $type
     * @param int $toId
     * @return CmsTemplateHtml|null
     */
    protected function getHtml($template, $type, $toId)
    {
        if ($type == 'single') {
            $tplHtml = $this->htmlModel->where(['type' => $type, 'template_id' => $template['id'], 'to_id' => $toId])
                ->cache('cms_page_' . $template['id'] . '_' . $type . '_' . $toId, $this->cacheTime, 'cms_html')
                ->find();
            return $tplHtml;
        }

        $pageInfo = $this->pageModel->where(['html_type' => $type, 'template_id' => $template['id'], 'to_id' => $toId])
            ->cache('cms_page_' . $template['id'] . '_' . $type . '_' . $toId, $this->cacheTime, 'cms_page')
            ->find(); //获取绑定的模板

        if ($pageInfo) {
            $tplHtml = $this->htmlModel->where('id', $pageInfo['html_id'])
                ->cache('cms_html_' . $pageInfo['html_id'], $this->cacheTime, 'cms_html')
                ->find();
            if (!$tplHtml) {
                $pageInfo->delete(); //删除无效的绑定记录
            }
            return $tplHtml;
        }

        return null;
    }

    protected function getWconfig()
    {
        $config = Module::getInstance()->config();
        unset($config['allow_tables'], $config['editor']);
        return $config;
    }

    /**
     * 处理静态资源路径
     * @param array|CmsTemplate $template
     * @return array
     */
    public function copyStatic($template)
    {
        $staticPath = 'theme/' . $template['view_path'] . DIRECTORY_SEPARATOR . 'static';
        $staticPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $staticPath);
        $staticDir = 'theme' . DIRECTORY_SEPARATOR . $template['view_path'];

        if (is_dir(App::getPublicPath() . $staticDir)) {
            file_put_contents(App::getPublicPath() . $staticDir . DIRECTORY_SEPARATOR . 'version.txt', date('Y-m-d-H:i:s'));
            if (is_file(App::getPublicPath() . $staticDir . DIRECTORY_SEPARATOR . 'no-publish.txt')) {
                return ['code' => 0, 'msg' => '[静态资源]发布取消：目录中存在no-publish.txt文件，已关闭资源发布模式。' . "{$staticPath} => public" . DIRECTORY_SEPARATOR . "{$staticDir}"];
            }
            Tool::copyDir(App::getPublicPath() . $staticDir, App::getPublicPath() . $staticDir . '__bak' . DIRECTORY_SEPARATOR . date('YmdHis'));
        }
        Tool::deleteDir(App::getPublicPath() . $staticDir);
        $res = Tool::copyDir(App::getRootPath() . $staticPath, App::getPublicPath() . $staticDir);
        if ($res) {
            file_put_contents(
                App::getPublicPath() . $staticDir . DIRECTORY_SEPARATOR . '不要修改此目录中文件.txt',
                '此目录是存放模板静态资源的，' . "\n"
                . '不要修改、替换文件或上传新文件到此目录及子目录，' . "\n"
                . '否则重新发布模板资源后改动文件将还原或丢失，' . "\n"
                . '原始文件存放于' . $staticPath . '目录下。' . "\n"
                . '请修改原始文件，再发布静态资源到此目录。' . "\n"
                . '如果您不想使用此模式，请在此位置新建文件：no-publish.txt，以避免修改被覆盖。' . "\n"
            );

            $directory = new \DirectoryIterator(App::getPublicPath() . $staticDir . '/css/');

            $v = $this->getStaticVersion($template['view_path']);
            $dir = '/theme/' . $template['view_path'] . '/';

            foreach ($directory as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() === "css") {
                    $css = file_get_contents($fileinfo->getPathname());
                    $css = $this->replaceCssImgPath($css, $v, $dir);
                    file_put_contents($fileinfo->getPathname(), $css);
                }
            }
            return ['code' => 1, 'msg' => '[静态资源]发布成功：' . "{$staticPath} => public" . DIRECTORY_SEPARATOR . "{$staticDir}"];
        }

        return ['code' => 0, 'msg' => '[静态资源]发布失败：' . "{$staticPath} => public" . DIRECTORY_SEPARATOR . "{$staticDir}"];
    }

    /**
     * 替换静态资源路径
     * @param array|mixed $template
     * @param string $content
     * @return string
     */
    public function replaceStaticPath($template, $content)
    {
        if (!is_dir('theme' . DIRECTORY_SEPARATOR . $template['view_path'] . DIRECTORY_SEPARATOR)) {
            return $content;
        }

        $staticDir = '/theme/' . $template['view_path'] . '/';
        $v = $this->getStaticVersion($template['view_path']);

        $content = preg_replace('/(<link\s+[^>]*?href=[\'\"])(?:\.{1,2}\/)?static\/([^>]+?\.\w+)([\'\"])/is', "$1{$staticDir}$2?v={$v}$3", $content);
        $content = preg_replace('/(<script\s+[^>]*?src=[\'\"])(?:\.{1,2}\/)?static\/([^>]+?\.js)([\'\"])/is', "$1{$staticDir}$2?v={$v}$3", $content);
        $content = preg_replace('/(<img\s+[^>]*?src=[\'\"])(?:\.{1,2}\/)?static\/([^>]+?\.\w+)([\'\"])/is', "$1{$staticDir}$2?v={$v}$3", $content);

        return $content;
    }

    public function getStaticVersion($viewPath)
    {
        $versionFilePath = App::getPublicPath() . 'theme' . DIRECTORY_SEPARATOR . $viewPath . DIRECTORY_SEPARATOR . 'version.txt';
        if (is_file($versionFilePath)) {
            $v = file_get_contents($versionFilePath);
            return $v;
        } else {
            $v = date('Y-m-d-H:i:s');
            if (file_put_contents($versionFilePath, $v)) {
                return $v;
            }
        }

        return '1';
    }

    /**
     * 替换css中图片路径
     * @param string $content
     * @param string $v
     * @param string $staticDir
     * @return string
     */
    protected function replaceCssImgPath($content, $v, $staticDir)
    {
        $content = preg_replace('/(background\s*:[^>]*?url\([\'\"]?)(?:\.{1,2}\/)?static\/([^\'\"\);]+?\.\w+)([\'\"]?)/is', "$1{$staticDir}$2?v={$v}$3", $content);
        $content = preg_replace('/(background\-img\s*:[^>]*?url\([\'\"]?)(?:\.{1,2}\/)?static\/([^\'\"\);]+?\.\w+)([\'\"]?)/is', "$1{$staticDir}$2?v={$v}$3", $content);

        return $content;
    }
}
