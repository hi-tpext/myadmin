# 欢迎使用

## 一、安装：
框架基于thinkphp 5.1*，有两种安装方式：全新安装/更新安装
1. 全新安装 **推荐**
> 安装 thinkphp 5.1.*，`myadmin`为新项目名称，可自行调整
```bash
composer create-project topthink/think=5.1.* myadmin
```
> 进入新项目根目录：`myadmin`

```bash
cd myadmin
```
> 安装后台扩展
```bash
composer require ichynul/tpextmyadmin
```
2. 更新安装

> 使用`git`命令克隆此仓库，最后一个`myadmin`为新项目名称，可自行调整
```bash
git clone https://gitee.com/ichynul/myadmin.git myadmin
```
进入新项目根目录：`myadmin`
```bash
cd myadmin
```
> 使用`composer`命令更新依赖
```bash
composer update
```

## 二、配置：
1. 创建mysql数据库和用户，正确配置到 `config/database.php`【重要】若后续访问页面报错，请回头检查这一步。
2. 配置`apache/nginx`及重写规则，略(自行百度) **重要**
没配置`重写规则`的话后续的url中加上`index.php` 如：
`http://localhost:8081/index.php/admin`

## 三、扩展安装：
1. 打开 `http://localhost:8081/admin`　首次访问会自动跳转到：
`http://localhost:8081/admin/extension/index` 安装页面
2. 安装 `[tpext.myadmin]`
3. 安装其余装扩展

## 四、登录后台：
再次打开 `http://localhost:8081/admin` ，会跳转登录，默认账号：
`admin`
`tpextadmin`

## 五、文档
<https://gitee.com/ichynul/myadmin/wikis/pages>

## 六、演示
#### 网址：
1.  <http://www.tpext.top/admin>　
2.  <http://quick.shenzhuo.vip:10582/admin>
#### 账号：
`admin`
`tpextadmin`

## 7.功能特性:
1. 模块化开发，核心功能都是通过composer安装的
#### 主要扩展依赖：
* [tpext] <https://gitee.com/ichynul/tpext> 扩展核心
* [tpextbuilder] <https://gitee.com/ichynul/tpextbuilder> UI 生成器
* [tpextmanager] <https://gitee.com/ichynul/tpextmanager> 管理工具
* [lightyearadmin]<https://gitee.com/ichynul/lightyearadmin> 基础样式库
* [tpextmyadmin] <https://gitee.com/ichynul/tpextmyadmin> 集成后台基础功能：权限、设置等
2. `tpextbuilder`UI模块基于`bootstrap`和`Light-Year-Admin-Template`的后台模板， 封装了大部分常用组件 ：
`Column`、`Row`、`Tab`、`Table`、`Form`、`Toolbar`、`Layer`、`Content`
3. `HasBuilder`封装了常用操作
#### 实例：
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
        $searchData = request()->post();
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
## 8.效果展示:

[![](https://gitee.com/ichynul/myadmin/raw/master/images/1.png)](https://gitee.com/ichynul/myadmin/raw/master/images/1.png "1")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/2.png)](https://gitee.com/ichynul/myadmin/raw/master/images/2.png "2")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/3.png)](https://gitee.com/ichynul/myadmin/raw/master/images/3.png "3")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/4.png)](https://gitee.com/ichynul/myadmin/raw/master/images/4.png "4")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/5.png)](https://gitee.com/ichynul/myadmin/raw/master/images/5.png "5")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/6.png)](https://gitee.com/ichynul/myadmin/raw/master/images/6.png "6")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/7.png)](https://gitee.com/ichynul/myadmin/raw/master/images/7.png "7")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/8.png)](https://gitee.com/ichynul/myadmin/raw/master/images/8.png "8")