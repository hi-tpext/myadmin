<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'tpext_find_extensions' => [
            'app\event\Extensions',
        ],
    ],

    'subscribe' => [
    ],
];
