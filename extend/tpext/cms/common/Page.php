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

use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\taglib\Processer;
use tpext\cms\common\taglib\Table;

class Page
{
    /**
     * @var CmsTemplate
     */
    protected $template = null;

    protected $cacheTime = 3600 * 24 * 7;

    public function __construct()
    {
        $this->template = new CmsTemplate;
    }

    /**
     * 首页
     * @param int $tpl_id
     * @return string
     */
    public function index($tpl_id)
    {
        $template = $this->template->where('id', $tpl_id)
            ->cache('cms_template_' . $tpl_id, $this->cacheTime, 'cms_template')
            ->find();

        if (!$template) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>未能找到them-' . $tpl_id . '</h4></body></html>';
        }

        $render = new Render();
        $res = $render->index($template);
        if ($res['code'] == 0) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>' . $res['msg'] . '</h4></body></html>';
        }
        return $res['data'];
    }

    /**
     * 栏目
     * @param int $id
     * @param int $tpl_id
     * @param int $page
     * @return string
     */
    public function channel($id, $tpl_id, $page = 1)
    {
        $template = $this->template->where('id', $tpl_id)
            ->cache('cms_template_' . $tpl_id, $this->cacheTime, 'cms_template')
            ->find();

        if (!$template) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>未能找到them-' . $tpl_id . '</h4></body></html>';
        }

        $table = 'cms_channel';
        $dbNameSpace = Processer::getDbNamespace();
        $channelScope = Table::defaultScope($table);
        $channel = $dbNameSpace::name($table)->where('id', $id)
            ->where($channelScope)
            ->cache('cms_channel_' . $id, $this->cacheTime, $table)
            ->find();

        if (!$channel) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>404</title></head><body><h4>栏目不存在-' . $id . '</h4></body></html>';
        }

        if ($channel['link']) {
            $redirectUrl = Processer::resolveWebPath($channel['link']);
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>跳转...</title><meta http-equiv="refresh" content="0;url=' . $redirectUrl . '"></head><body></body></html>';
        }

        $channel = Processer::detail($table, $channel);
        $render = new Render();
        $res = $render->channel($template, $channel, $page);
        if ($res['code'] == 0) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>' . $res['msg'] . '</h4></body></html>';
        }
        return $res['data'];
    }

    /**
     * 内容
     * @param int $id
     * @param int $tpl_id
     * @param int $is_static
     * @return string
     */
    public function content($id, $tpl_id, $is_static = 0)
    {
        $template = $this->template->where('id', $tpl_id)
            ->cache('cms_template_' . $tpl_id, $this->cacheTime, 'cms_template')
            ->find();

        if (!$template) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>未能找到them-' . $tpl_id . '</h4></body></html>';
        }

        $table = 'cms_content';
        $dbNameSpace = Processer::getDbNamespace();
        $contentScope = Table::defaultScope($table);
        $content = $dbNameSpace::name($table)
            ->where('id', $id)
            ->where($contentScope)
            ->cache('cms_content_' . $id, $this->cacheTime, $table)
            ->find();

        if (!$content) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>404</title></head><body><h4>内容不存在-' . $id . '</h4></body></html>';
        }

        if ($content['link']) {
            $redirectUrl = Processer::resolveWebPath($content['link']);
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>跳转...</title><meta http-equiv="refresh" content="0;url=' . $redirectUrl . '"></head><body></body></html>';
        }

        $content = Processer::detail($table, $content);
        $render = new Render();
        $res = $render->content($template, $content, $is_static);
        if ($res['code'] == 0) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>' . $res['msg'] . '</h4></body></html>';
        }
        return $res['data'];
    }

    /**
     * 内容点击
     * 
     * @param int $id
     * @return int
     */
    public function click($id)
    {
        $render = new Render();
        return $render->click($id);
    }

    /**
     * 动态页面
     * @param int $html_id
     * @param int $tpl_id
     * @return string
     */
    public function dynamic($html_id, $tpl_id)
    {
        $template = $this->template->where('id', $tpl_id)
            ->cache('cms_template_' . $tpl_id, $this->cacheTime, 'cms_template')
            ->find();

        if (!$template) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>未能找到them-' . $tpl_id . '</h4></body></html>';
        }

        $render = new Render();
        $res = $render->dynamic($template, $html_id);
        if ($res['code'] == 0) {
            return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"/><title>500</title></head><body><h4>' . $res['msg'] . '</h4></body></html>';
        }
        return $res['data'];
    }
}
