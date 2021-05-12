<?php
//配置模板，注意，可以增加修改配置键，不要直接修改此文件配置值,里面的值只是默认值，真实值保存在数据库
return [
    //
    'lvup_1' => '充值719',
    'lvup_2' => 30,
    'lvup_3' => 200,
    'lvup_4' => 500,
    'lvup_5' => 1000,
    'lvup_6' => 4,
    //
    '__config__' => function (\tpext\builder\common\Form $form, &$data) {
        //
        $form->text('lvup_1', '等级1');
        $form->text('lvup_2', '等级2');
        $form->text('lvup_3', '等级3');
        $form->text('lvup_4', '等级4');
        $form->text('lvup_5', '等级5');
        $form->text('lvup_6', '等级6');
    },
];
