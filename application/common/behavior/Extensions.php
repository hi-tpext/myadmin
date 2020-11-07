<?php

namespace app\common\behavior;

use tpext\common\ExtLoader;

class Extensions
{
    public function run()
    {
        $classMap = [
            'extdemo\\common\\Module',
            //其他自定义扩展
        ];

        ExtLoader::addClassMap($classMap);
    }
}