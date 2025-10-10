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
use tpext\cms\common\event;
use tpext\common\ExtLoader;
use tpext\common\Module as baseModule;
use tpext\cms\common\model\CmsTemplate;
use tpext\cms\common\model\CmsTemplateHtml;

class Module extends baseModule
{
    protected $version = '2.0.13';

    protected $name = 'tpext.cms';

    protected $title = 'tpext cms框架';

    protected $description = '内容管理功能';

    protected $root = __DIR__ . '/../';

    /**
     * 后台菜单
     *
     * @var array
     */
    protected $menus = [
        [
            'title' => '内容管理',
            'sort' => 1,
            'url' => '#',
            'icon' => 'mdi mdi-library-books',
            'children' => [
                [
                    'title' => '内容管理',
                    'sort' => 1,
                    'url' => '/admin/cmscontent/index',
                    'icon' => 'mdi mdi-book-open'
                ],
                [
                    'title' => '栏目管理',
                    'sort' => 2,
                    'url' => '/admin/cmschannel/index',
                    'icon' => 'mdi mdi-file-tree'
                ],
                [
                    'title' => '模板管理',
                    'sort' => 3,
                    'url' => '/admin/cmstemplate/index',
                    'icon' => 'mdi mdi-json'
                ],
                [
                    'title' => '广告位置',
                    'sort' => 4,
                    'url' => '/admin/cmsposition/index',
                    'icon' => 'mdi mdi-bullhorn'
                ],
                [
                    'title' => '广告管理',
                    'sort' => 5,
                    'url' => '/admin/cmsbanner/index',
                    'icon' => 'mdi mdi-image-multiple'
                ],
                [
                    'title' => '合集管理',
                    'sort' => 6,
                    'url' => '/admin/cmstag/index',
                    'icon' => 'mdi mdi-tag-outline'
                ],
                [
                    'title' => '模型管理',
                    'sort' => 7,
                    'url' => '/admin/cmscontentmodel/index',
                    'icon' => 'mdi mdi-buffer'
                ],
                [
                    'title' => '字段管理',
                    'sort' => 7,
                    'url' => '/admin/cmscontentfield/index',
                    'icon' => 'mdi mdi-playlist-plus'
                ],
                [
                    'title' => '设置',
                    'sort' => 8,
                    'url' => '/admin/config/edit?key=tpext-cms-common-Module',
                    'icon' => 'mdi mdi-settings'
                ]
            ]
        ]
    ];

    protected $modules = [
        'admin' => [
            'cmschannel',
            'cmscontent',
            'cmsposition',
            'cmsbanner',
            'cmstag',
            'cmstemplate',
            'cmstemplatehtml',
            'cmstemplatestatic',
            'cmstemplatemake',
            'cmscontentmodel',
            'cmscontentfield',
        ],
    ];

    public function install()
    {
        $success = parent::install();

        if ($success) {
            $view_path = App::getRootPath();

            try {
                CmsTemplate::initPath($view_path . 'theme/default');
                CmsTemplateHtml::scanPageFiles(1, $view_path . 'theme/default');
                Tool::deleteDir(App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'theme');
                $render = new Render;
                $tpls = CmsTemplate::select();
                foreach ($tpls as $tpl) {
                    $render->copyStatic($tpl);
                }
                $routeBuilder = new RouteBuilder;
                $routeBuilder->builder(true);
            } catch (\Throwable $e) {
                trace($e->__toString());
            }

            $tags = ['cms_html', 'cms_page', 'cms_template', 'cms_channel', 'cms_content', 'cms_position', 'cms_banner', 'cms_tag'];

            foreach ($tags as $tag) {
                Cache::deleteTag($tag);
            }
        }

        return $success;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function afterCopyAssets()
    {
        Tool::deleteDir(App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'theme');
        return true;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function uninstall($runSql = true)
    {
        Tool::deleteDir(App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'theme');
        return parent::uninstall($runSql);
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function upgrade()
    {
        Tool::deleteDir(App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'theme');
        return parent::upgrade();
    }

    public function loaded()
    {
        $makeMtatic = self::config('make_static', 1);

        if ($makeMtatic) {
            $maker = new event\MakeStatic;
            $maker->watch();
        }

        $maker = new event\MakeRoute;
        $maker->watch();

        $maker = new event\MakeTemplate;
        $maker->watch();

        //tp5.1模型事件处理
        if (ExtLoader::isTP51()) {
            $maker = new event\ModelEvent;
            $maker->watch();
        }
    }
}
