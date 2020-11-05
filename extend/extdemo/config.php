<?php

return [
    'name' => '自定义扩展范例',
    'description' => '可通过tp框架的`extend`目录来安装扩展',
    //配置描述
    '__config__' => [
        'name' => ['type' => 'text', 'label' => '名称'],
        'description' => ['type' => 'textarea', 'label' => '描述']
    ],
];
