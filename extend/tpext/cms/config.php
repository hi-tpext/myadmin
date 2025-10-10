<?php

return [
    'name' => 'Tpext CMS系统',
    'keywords' => 'Tpext CMS系统,cms,内容管理系统,thinkphp,php',
    'description' => 'Tpext CMS系统',
    'copyright' => 'Copyright &copy; ' . date('Y') . '. <a target="_blank" href="#">Tpext CMS系统</a> All rights reserved.',
    'editor' => 'wangEditor',
    'use_layout' => 1,
    'make_static' => 0,
    'allow_tables' => '',
    //配置描述
    '__config__' => [
        'name' => ['type' => 'text', 'label' => '网站名称'],
        'keywords' => ['type' => 'textarea', 'label' => '关键字', 'help' => '多个关键字之间用英文逗号分隔'],
        'description' => ['type' => 'textarea', 'label' => '描述'],
        'copyright' => ['type' => 'textarea', 'label' => '版权'],
        'editor' => ['type' => 'radio', 'label' => '编辑器', 'options' => ['wangEditor' => 'wangEditor', 'ueditor' => 'UEditor', 'ckeditor' => 'CKEditor', 'mdeditor' => 'MDEditor', 'tinymce' => 'Tinymce']],
        'use_layout' => ['type' => 'radio', 'label' => '使用继承布局', 'options' => ['0' => '否', '1' => '是'], 'help' => '若开启，新建模板页面使用`layout`继承模式。'],
        'make_static' => ['type' => 'radio', 'label' => '生成静态', 'options' => ['0' => '否', '1' => '是'], 'help' => '若开启，文章或栏目修改后，自动生成静态页面。如果不想使用静态生成，请设置为否，然后手动删除已生成的静态文件。'],
        'allow_tables' => ['type' => 'textarea', 'label' => '允许表名', 'help' => '除cms默认的表外，允许使用cms标签的表名（不包含表前缀），多个表名之间用英文逗号分隔'],
    ],
];
