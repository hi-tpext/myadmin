<?php

namespace extdemo\common;

use tpext\common\Module as baseModule;

class Module extends baseModule
{
    protected $version = '1.0.1';

    protected $name = 'tpext.extdemo';

    protected $title = '自定义扩展范例';

    protected $description = '可通过tp框架的`extend`目录来安装扩展';

    protected $root = __DIR__ . '/../';

    protected $assets = 'assets';

    protected $modules = [
        'admin' => ['extdemo'],
    ];

    /**
     * 后台菜单
     *
     * @var array
     */
    protected $menus = [
        [
            'title' => '自定义扩展',
            'sort' => 1,
            'url' => '#',
            'icon' => 'mdi mdi-coffee',
            'children' => [
                [
                    'title' => '扩展范例',
                    'sort' => 1,
                    'url' => '/admin/extdemo/index',
                    'icon' => 'mdi mdi-collage',
                ]
            ],
        ]
    ];
}
