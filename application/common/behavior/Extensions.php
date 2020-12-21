<?php

namespace app\common\behavior;

use tpext\common\ExtLoader;

class Extensions
{
    public function run()
    {
        /*也可在 【扩展管理－tpext综合管理－配置】中添加 */
        $classMap = [
            'extdemo\\common\\Module',
            //其他自定义扩展
            'myadmindata\\common\\Module',
        ];

        ExtLoader::addClassMap($classMap);
    }
}