自定义扩展范例：

某些情况下，不便于公开通过`composer`来安装扩展，可通过tp框架的`extend`目录来安装扩展。

自定义扩展与`composer`扩展目录比较

假如有一个`composer`扩展`extdemo`，那么他的目录大概是这样：
扩展根目录：`wwwroot\vendor\ichynul\extdemo\`
```
── assets                    (资源，不需则留空，发布时复制到`/public/assets/`)
    ├── css
    ├── images
    └── js
── data                      (安装或卸载脚本，不需要安装则留空)
    ├── install.sql
    └── uninstall.sql
── src                       （源码目录）
    ├── admin                     (admin模块)
    │    ├── controller
    │    ├── ...
    │    ├── model
    │    ├── ...
    └── common
    │    └── Module.php             (模块定义)
    ├── common.php
    ├── config.php                  (扩展自定义配置)
    └── helper.php　　　　　　　　　　　(扩展加载)
── composer.json
── LICENSE.txt
── README.md
```
PS : tp框架的`extend`加载原理。自动查找`extend`目录下的目录和文件，根据目录结构转换为对应的命名空间。
`thinkphp\library\Loader.php` （line:114）:
```php
// 自动加载extend目录
self::addAutoLoadDir($rootPath . 'extend');

```

把它改造为自定义扩展：
根目录：`wwwroot\extend\extdemo\`

```
── assets                    (资源，不需则留空，发布时复制到`/public/assets/`)
    ├── css
    ├── images
    └── js
── data                      (安装或卸载脚本，不需要安装则留空)
    ├── install.sql
    └── uninstall.sql
── admin                     (admin模块)
    ├──controller
    ├──...
    ├──model
    ├──...
── common
    └── Module.php             (模块定义)
── src
    └── config.php                  (扩展自定义配置)
── common.php
── LICENSE.txt
── README.md
```
改造要点：
1. 不需要`composer.json`。
2. 不需要`src`。代码直接放扩展根目录。
3. 不需要`helper.php`。
4. 修改`Module.php`中`$root`定义，由于代码从`src`目录往上提了一级，所以：
`protected $root = __DIR__ . '/../../';` 改为： `protected $root = __DIR__ . '/../';`
5. 由于不能通过`helper.php`来加载扩展，所以需要监听`tpext_find_extensions`事件，在查找扩展前把自定义加入进去。

***tp5.1***:

新建文件`application\common\behavior\Extensions.php`:

```php
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

```

编辑文件：`application\tags.php`，添加`tpext_find_extensions`键，数组里面写入：`app\\common\\behavior\\Extensions`:
```php
<?php

// 应用行为扩展定义文件
return [
    // 应用初始化
    'app_dispatch' => [],
    // 应用开始
    'app_begin' => [],
    // 模块初始化
    'module_init' => [],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter' => [],
    // 日志写入
    'log_write' => [],
    // 应用结束
    'app_end' => [],
    // 扩展加载
    'tpext_find_extensions' => [
        'app\\common\\behavior\\Extensions',
    ],
];

```

6. 修改`LICENSE.txt`文件
7. 修改`readme.md`，由于自定义插件不能加载其他`composer`扩展，若你的自定义扩展依赖于其他`composer`扩展，可在其中说明需要安装哪些。
8. 目前只支持一级目录的扩展：
```
── extend
    ├── mymouule1（支持）
    ├── mymouule2（支持）
    └── hismouule3（支持）

```
不支持二级的扩展，如：
```
── extend
    └── mymouule
    │    ├── mymouule1 （二级目录下不支持）
    │    └── mymouule2 （二级目录下不支持）
    └── hismouule3（支持）
```
所有如果你同时使用其他人的扩展，给自定义扩展起名是要避免命名冲突