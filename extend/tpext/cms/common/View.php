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

use think\Template;
use tpext\think\App;
use tpext\common\ExtLoader;

class View
{
    protected $vars = [];
    protected $content = null;
    protected $tpl = '';

    protected $app;

    /**
     * Undocumented variable
     *
     * @var Template
     */
    protected $engine;

    public function __construct($tpl = '', $vars = [], $config = [])
    {
        $this->tpl = $tpl;
        $this->vars = $vars;

        $config = array_merge([
            'taglib_build_in' => '\\tpext\\cms\\common\\taglib\\Cms,cx',
            'taglib_pre_load' => '\\tpext\\cms\\common\\taglib\\Cms',
            'tpl_deny_func_list' => 'eval,echo,exit,exec,shell_exec',
            'tpl_deny_php' => true,
            'cache_prefix' => 'tpex.tcms',
            'tpl_begin' => '{',
            'tpl_end' => '}',
            'taglib_begin' => '{',
            'taglib_end' => '}',
            'cache_path' => App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR,
        ], $config);


        if (ExtLoader::isTP51()) {
            $this->app = app();
            $this->engine = new Template($this->app, $config);
        } else {
            $this->engine = new Template($config);
        }
    }

    /**
     * 获取输出数据
     * @access public
     * @return string
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = $this->fetch($this->tpl) ?: '';
        }

        return $this->content;
    }

    protected function fetch($template = '')
    {
        ob_start();

        if (PHP_VERSION > 8.0) {
            ob_implicit_flush(false);
        } else {
            ob_implicit_flush(0);
        }

        try {
            $this->engine->fetch($template, $this->vars);
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $content = ob_get_clean();

        return $content;
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
