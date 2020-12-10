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
common
    └── Module.php             (模块定义)
── config.php                  (扩展自定义配置)
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
5. 由于不能通过`helper.php`来加载扩展，所以需要在【扩展管理】-【tpext基础】的配置中加入`extdemo\common\Module`，刷新。
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