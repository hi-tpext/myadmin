# tpext.cms 内容管理系统

## 功能

- 文章管理
- 分类管理
- 合集管理
- 模板管理
- 静态资源`js/cs`s 管理
- 模板绑定
- 静态页面生成
- 动态路由生成

## 安装

2024-12-10 更新：不支持过往版本的直接升级，建议新项目使用。

如果是老项目，请删除原数据表（建议重命名以备份），然后再安装。

```sql
DROP TABLE IF EXISTS `__PREFIX__cms_category`;
DROP TABLE IF EXISTS `__PREFIX__cms_content`;
DROP TABLE IF EXISTS `__PREFIX__cms_position`;
DROP TABLE IF EXISTS `__PREFIX__cms_banner`;
DROP TABLE IF EXISTS `__PREFIX__cms_tag`;
```

### 一些细节

`nginx` 默认文档问题

如宝塔建站的默认顺序为：`index.php index.html index.htm default.php default.htm default.html`。

那么当访问 `http://yourdomain.com/` 时其实是访问`index.php`，当访问 `http://yourdomain.com/index.html` 才是访问`index.html`。

如开启首页静态化，并想让用户始终访问到`index.html`，请设置 `nginx` 默认文档为第一个为`index.html`:

```nginx
server
{
    listen 80;
    server_name new.ynyysc.com;
    index index.html index.php index.htm default.php default.htm default.html;
}
```

### tp6 / tp8

如果不使用首页静态化（`index.html`不存在），多应用模式下，如存在`app/index`目录，将可能影响 `http://yourdomain.com/`可访问性。

`http://yourdomain.com/`请求或访问到默认应用，当于访问`http://yourdomain.com/index`。

因为`think-multi-app`的逻辑是存在`app/index`目录时，`http://yourdomain.com/index` 的请求都不走路由，cms系统生成的路由也失效。

解决方法：
`/config/app.php`中修改默认应用为一个不存在的应用名，如`none`

```php
`default_app` => 'none'
```

当然，如果你的生成路径不是 `/`，比如是 `/cms/` 那么以上问题不存在。
