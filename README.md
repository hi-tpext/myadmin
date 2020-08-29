# 欢迎使用

## 1.安装:
框架基于thinkphp 5.1*，有两种安装方式：全新安装/更新安装
### a.全新安装[推荐]
>安装 thinkphp 5.1.*,`myadmin`为新项目名称，可自行调整
```bash
composer create-project topthink/think=5.1.* myadmin
```
>安装后台扩展

```bash
cd myadmin
```
```bash
composer require ichynul/tpextmyadmin
```
### b.更新安装
>在现有基础上更新
```bash
git clone https://gitee.com/ichynul/myadmin.git
```
```bash
cd myadmin
```
```bash
composer update
```

## 2.配置:
- 创建mysql数据库和用户，正确配置到 `config/database.php`【重要】若后续访问页面报错，请回头检查这一步。
- apache/nginx及配置、重写规则，略(自行百度)【重要】。没配置`重写规则`的话后续的url中加上`index.php` 如　`http://localhost:8081/index.php/admin`

## 3.扩展安装:
- 打开 `http://localhost:8081/admin`　首次访问会自动跳转到 `http://localhost:8081/admin/extension/index` 安装页面
- 安装 `[tpext.myadmin]`,
- 安装其余装扩展

## 4.登录后台:
- 再次打开 `http://localhost:8081/admin` ，会跳转登录 默认账号 admin tpextadmin

## 5.文档
<https://gitee.com/ichynul/myadmin/wikis/pages>

## 6演示
>网址： <http://www.tpext.top/admin/>
>admin
>tpextadmin

尽量不要上传大文件，空间不足

## 7.功能特性:
- 模块化开发（所以不要研究此git仓库，除了`README.md`外，其余基本就是一个空的的tp5.1，核心功能都是通过composer安装的！）
**扩展依赖**
>tpext           <https://gitee.com/ichynul/tpext>

>tpextbuilder    <https://gitee.com/ichynul/tpextbuilder>

>tpextmanager    <https://gitee.com/ichynul/tpextmanager>

>lightyearadmin  <https://gitee.com/ichynul/lightyearadmin>

>tpextmyadmin    <https://gitee.com/ichynul/tpextmyadmin>

- 基于 [bootstrap]和[Light-Year-Admin-Template]的后台模板， 封装了大部分常用组件。
### [tpextbuilder] 简介
### 基础组件：Column,Row,Tab,Table,Form,Toolbar,Layer,Content

#### Column ： 灵活的 col栅格布局，自由组合各个组件
###### 实例
`HasBuilder`封装了常用操作

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

        //作为下拉选择数据源　相关设置
        $this->selectTextField = '{id}#{nickname}({mobile})';//显示
        $this->selectFields = 'id,nickname,mobile';// ->field('id,nickname,mobile') 字段，配合[显示],优化查询性能
        $this->selectSearch = 'username|nickname|mobile'; //关键字　like　查询字段　->where('username|nickname|mobile', 'like', $kwd);
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
        $form->radio('gender', '性别')->options([0 => '未知', 1 => '男', 2 => '女'])->default(0);
        $form->number('age', '年龄')->max(100)->min(1)->default(18);

        if ($isEdit) {
            $form->show('points', model\MemberAccount::$types['points']);
            $form->show('money', model\MemberAccount::$types['money']);
        }

        $form->tab('其他信息');
        $form->fields('省/市/区');
        $form->select('province', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $form->select('city', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $form->select('area', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );
        $form->fieldsEnd();

        $form->textarea('remark', '备注')->maxlength(255);
        $form->switchBtn('status', '状态')->default(1);
        $form->image('erweima_img', '二维码')->thumbSize(50, 50);

        $levels = model\MemberLevel::order('level')->field('name,level')->select();

        if ($isEdit) {
            $form->match('level', '等级')->optionsData($levels, 'name', 'level');

            $form->show('last_login_ip', '最近登录IP')->default('-');

            if (session('admin_id') == 1) {
                $form->show('openid', 'openid')->default('-');
            }

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
            'level',
            'province',
            'city',
            'area',
            'status',
            'remark',
            'erweima_img',
        ], 'post');

        $result = $this->validate($data, [
            'username|账号' => 'require',
            'nickname|昵称' => 'require',
            'mobile|手机号' => 'mobile',
            'level|等级' => 'number',
            'age|年龄' => 'number',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($data['mobile'] && $exist = $this->dataModel->where(['mobile' => $data['mobile']])->find()) {
            if ($id) {
                if ($exist['id'] != $id) {
                    $this->error('手机号未能修改，已被占用');
                }
            } else {
                $this->error('手机号已被占用');
            }
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