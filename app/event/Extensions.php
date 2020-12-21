<?php
declare (strict_types = 1);

namespace app\event;

use tpext\common\ExtLoader;

class Extensions
{
    public function handle()
    {
        // 事件监听处理

        $classMap = [
            'extdemo\\common\\Module',
            //其他自定义扩展
            'myadmindata\\common\\Module',
        ];

        ExtLoader::addClassMap($classMap);
    }
}
