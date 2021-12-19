# 欢迎使用

## 一、安装

### 框架有两个版本

- `5.1` 基于thinkphp 5.1 (**推荐(文档基于此版本)**)

- `6.0` 基于thinkphp 6.0 (**beta**)

### 框架需要使用 composer和git

安装 `composer`

<https://pkg.phpcomposer.com/#how-to-install-composer/>

安装 `git`

<https://git-scm.com/>

### 使用 `composer` 全新安装

> 安装 thinkphp(`5.1` 或 `6.0`，根据您的需要，选择其中一个版本)，

 [5.1]分支对应 `tpextmyadmin`的[1.0]分支，依次执行以下命令，`myadmin` 为新项目目录，可自行调整

```bash
composer create-project topthink/think=5.1.* myadmin

cd myadmin

composer require ichynul/tpextmyadmin:^1.0
```

或

[6.0]分支对应 `tpextmyadmin`的[3.0]分支，依次执行以下命令，`myadmin6` 为新项目目录，可自行调整

```bash
composer create-project topthink/think=6.0.* myadmin6

cd myadmin6

composer require ichynul/tpextmyadmin:^3.0
```

> 安装完毕，此安装版是最小模式，只包含基本的后台功能，建议开发新项目时使用此方式。

---

### 使用 `git` 安装演示站

> 拉取 `5.1` 分支代码，依次执行以下命令，`myadmin` 为新项目目录，可自行调整

```bash
git clone -b 5.1 https://github.com/hi-tpext/myadmin.git myadmin

cd myadmin

composer update
````

或 拉取 `6.0` 分支代码，依次执行以下命令，`myadmin6` 为新项目目录，可自行调整

```bash
git clone -b 6.0 https://github.com/hi-tpext/myadmin.git myadmin6

cd myadmin6

composer update
```

> 相关演示代码在`application/admin/`或`app/admin/`中，数据库脚本由`[myadmindata]`扩展提供，请下载安装。
> 安装完毕，此安装版是最和演示站同步的，如果你想自己搭建演示站可用此方式。
> 注意：此方式的仓库是不带`composer`依赖`vendor`目录和`thinkphp`目录的，请务必运行`composer update`安装所有依赖后再访问网站。

### 一些问题

- 细节很重要，安装过程中若有问题，请多检查。
- 比如创建项目后没进入新项目目录就运行命令，再比如跑到`public`里面运行命令的，网站运行目录没指向到`public`的。
- `composer`报错的版本冲突，php版本不符问题，php依赖缺失(如curl、zip)，php方法被禁用等。
- 因为涉及到的东西不可能所有的都列出来详细的讲。

---

## 二、配置

- `apache/nginx` 重写规则，略(自行百度) ***重要**

- 没配置`重写规则`的话后续的url中加上`index.php`

- 如：`http://localhost:8081/index.php/admin`

## 三、扩展安装

1. 浏览器输入 [`http://localhost:8081/admin`] 打开，如果没有事先配置数据库，将会跳转到配置数据库的页面。

2. 自动安装基础扩展

3. 手动安装 [`tpext.myadmin`]，确保此扩展优先，以支持其他扩展的后台菜单创建

4. 手动安装其余装扩展

## 四、登录后台

- 浏览器再次输入[`http://localhost:8081/admin`]打开，会跳转登录页面
- 默认账号：`admin`：`tpextadmin`
- 成功登录后台即说明安装完成

---

## 五、文档

<https://gitee.com/tpext/myadmin/wikis/pages>

## 六、演示

### 网址

1. [tp5.1] <http://f0vh2ohz.shenzhuo.vip:40035/admin>

2. [tp6.0] <http://f0vh2ohz.shenzhuo.vip:40036/admin>

### 账号

`admin`：`tpextadmin`

### 注意事项

- 请不要乱修改数据
- 请不要上传非法内容或图片
- 请不要上传您的重要数据到上面以防泄漏
- 有问题请联系：ichynul#163.com (#换@)，或通过群联系。
- 演示网站偶尔不稳定，会定期重启

## 七、功能特性

1.模块化开发，核心功能都是通过 `composer` 安装的

### 主要扩展依赖

- [tpext] <https://gitee.com/tpext/tpext> 扩展核心
- [tpextbuilder] <https://gitee.com/tpext/tpextbuilder> UI 生成器
- [tpextmanager] <https://gitee.com/tpext/tpextmanager> 管理工具
- [lightyearadmin]<https://gitee.com/tpext/lightyearadmin> 基础样式库
- [tpextmyadmin] <https://gitee.com/tpext/tpextmyadmin> 集成后台基础功能：权限、设置等
- [更多] <https://gitee.com/tpext/extensions/blob/main/extensions.json> 查看全部扩展介绍

2.`tpextbuilder`UI模块基于`bootstrap`和`Light-Year-Admin-Template`的后台模板， 封装了大部分常用组件 ：

`Column`、`Row`、`Tab`、`Table`、`Form`、`Toolbar`、`Layer`、`Content`

3.`HasBuilder` 封装了常用操作，可供控制器引入使用

#### 实例

```php
<?php

namespace app\admin\controller;

use app\common\logic\MemberLogic;
use app\common\model;
use think\Controller;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 会员管理
 */
class Member extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var model\Member
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new model\Member;
        $this->pageTitle = '会员管理';
        $this->enableField = 'status';
        $this->pagesize = 8;

        /* 作为下拉选择数据源　相关设置 */
        //显示
        $this->selectTextField = '{id}#{nickname}({mobile})';
        //like查询字段，$this->dataModel->where('username|nickname|mobile', 'like', $kwd);
        $this->selectSearch = 'username|nickname|mobile';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();
        $where = [];
        if (!empty($searchData['id'])) {
            $where[] = ['id', 'eq', $searchData['id']];
        }
        if (!empty($searchData['username'])) {
            $where[] = ['username', 'like', '%' . $searchData['username'] . '%'];
        }
        if (!empty($searchData['nickname'])) {
            $where[] = ['nickname', 'like', '%' . $searchData['nickname'] . '%'];
        }
        if (!empty($searchData['mobile'])) {
            $where[] = ['mobile', 'like', '%' . $searchData['mobile'] . '%'];
        }
        if (isset($searchData['status']) && $searchData['status'] != '') {
            $where[] = ['status', 'eq', $searchData['status']];
        }
        if (isset($searchData['level']) && $searchData['level'] != '') {
            $where[] = ['level', 'eq', $searchData['level']];
        }
        if (!empty($searchData['province'])) {
            $where[] = ['province', 'eq', $searchData['province']];
            if (!empty($searchData['city'])) {
                $where[] = ['city', 'eq', $searchData['city']];
                if (!empty($searchData['area'])) {
                    $where[] = ['area', 'eq', $searchData['area']];
                }
            }
        }
        return $where;
    }

    /**
     * 构建搜索
     *
     * @return void
     */
    protected function builSearch()
    {
        $search = $this->search;

        $search->text('id', '会员id')->maxlength(20);
        $search->text('username', '账号')->maxlength(20);
        $search->text('nickname', '昵称')->maxlength(20);
        $search->text('mobile', '手机号')->maxlength(20);
        $search->select('level', '等级')->optionsData(model\MemberLevel::order('level')->select(), 'name', 'level')->afterOptions([0 => '普通会员']);
        $search->select('status', '状态')->options([0 => '禁用', 1 => '正常']);
        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;

        $table->show('id', 'ID');
        $table->image('avatar', '头像')->thumbSize(50, 50)->default('/static/images/touxiang.png');
        $table->show('username', '账号');
        $table->text('nickname', '昵称')->autoPost()->getWrapper()->addStyle('width:130px');
        $table->show('mobile', '手机号')->getWrapper()->addStyle('width:100px');
        $table->match('gender', '性别')->options([1 => '男', 2 => '女', 0 => '未知'])->getWrapper()->addStyle('width:50px');
        $table->show('age', '性别');
        $table->show('level_name', '等级');
        $table->show('money', model\MemberAccount::$types['money']);
        $table->show('points', model\MemberAccount::$types['points']);
        $table->show('pca', '省市区');
        $table->switchBtn('status', '状态')->default(1)->autoPost()->getWrapper()->addStyle('width:60px');
        $table->show('last_login_time', '最近登录')->getWrapper()->addStyle('width:150px');
        $table->show('create_time', '注册时间')->getWrapper()->addStyle('width:150px');

        $table->sortable('id,sort,money,points,commission,re_comm,shares,last_login_time');

        $table->getToolbar()
            ->btnAdd()
            ->btnEnableAndDisable('启用', '禁用')
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnLink('account', url('/admin/memberaccount/add', ['member_id' => '__data.pk__']), '', 'btn-success', 'mdi-square-inc-cash');
    }

    /**
     * 构建表单
     *
     * @param boolean $isEdit
     * @param array $data
     */
    protected function builForm($isEdit, &$data = [])
    {
        $form = $this->form;

        $form->tab('基本信息');
        $form->image('avatar', '头像')->thumbSize(50, 50);
        $form->text('username', '账号')->required()->maxlength(20);
        $form->text('nickname', '昵称')->required()->maxlength(20);
        $form->text('mobile', '手机号')->maxlength(11);
        $form->text('email', '邮件')->maxlength(60);
        $form->number('age', '年龄')->max(100)->min(1)->default(18);
        $form->radio('gender', '性别')->options([0 => '未知', 1 => '男', 2 => '女'])->default(0);

        $form->tab('其他信息');
        $form->textarea('remark', '备注')->maxlength(255);
        $form->switchBtn('status', '状态')->default(1);

        if ($isEdit) {
            $form->show('last_login_time', '最近登录时间');
            $form->show('create_time', '注册时间');
            $form->show('update_time', '修改时间');
        }
    }

    /**
     * 保存数据
     *
     * @param integer $id
     * @return void
     */
    private function save($id = 0)
    {
        $data = request()->only([
            'avatar',
            'username',
            'nickname',
            'mobile',
            'email',
            'gender',
            'age',
            'status',
            'remark',
        ], 'post');

        $result = $this->validate($data, [
            'username|账号' => 'require',
            'nickname|昵称' => 'require',
            'mobile|手机号' => 'mobile',
            'age|年龄' => 'number',
        ]);

        if (true !== $result) {
            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
```

## 八、QQ群

<img src="https://images.gitee.com/uploads/images/2021/0527/115851_20d4c40a_306213.jpeg" width="180" style="width:180px;" />

## 九、捐助

如果您觉得我们的开源软件对你有所帮助，请扫下方二维码打赏我们一杯咖啡。

<img src="https://images.gitee.com/uploads/images/2021/0610/122853_5a765aac_306213.jpeg" width="180" style="width:180px;" />

## 十、效果展示

![alt 1](https://gitee.com/tpext/myadmin/raw/5.1/images/1.png "1")
![alt 2](https://gitee.com/tpext/myadmin/raw/5.1/images/2.png "2")
![alt 3](https://gitee.com/tpext/myadmin/raw/5.1/images/3.png "3")
![alt 4](https://gitee.com/tpext/myadmin/raw/5.1/images/4.png "4")
![alt 5](https://gitee.com/tpext/myadmin/raw/5.1/images/5.png "5")
![alt 6](https://gitee.com/tpext/myadmin/raw/5.1/images/6.png "6")
![alt 7](https://gitee.com/tpext/myadmin/raw/5.1/images/7.png "7")
![alt 8](https://gitee.com/tpext/myadmin/raw/5.1/images/8.png "8")

## 十一、鸣谢

`Tpext` 系列扩展和使用了以下框架技术或受到其启发：

- Thinkphp
- Botstrap
- Lightyear-admin
- Laravel-admin
- Jquery
- Layer
- Bootstrap-number-input
- Bootstrap-duallistbox
- Bootstrap-datepicker
- Bootstrap-daterangepicker
- Bootstrap-colorpicker
- Bootstrap-maxlength
- Bootstrap-touchspin
- Webuploader
- FontIconPicker
- Select2
- Dropzone
- ZTree
- Jstree
- CKEditor
- Editormd
- Tinymce
- UEditor
- WangEditor
- AreaCity-JsSpider-StatsGov
- 等

## 十二、License

Apache2
