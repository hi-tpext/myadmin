# 欢迎使用 

## 1.安装:
框架基于thinkphp 5.1*，有两种安装方式：
### a.全新安装
>安装 thinkphp 5.1.*
```bash
composer create-project topthink/think=5.1.* myadmin
```
>安装后台扩展

```bash
cd myadmin
```
```bash
composer require ichynul/tpextmyadmin>=1.1
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
- 正确配置 database数据库

## 3.扩展安装:
- 打开 `http://www.yourhost.com/admin/extension/index`
- 安装 `[tpext.core]`,
- 安装其余装扩展

## 4.登录后台:
- 打开 `http://www.yourhost.com/admin` ，会跳转登录 默认账号 admin tpextadmin

## 5.文档 
<https://gitee.com/ichynul/myadmin/wikis/pages>

## 6演示
>网址： <http://www.tpext.top/admin/>
>admin
>tpextadmin

尽量不要上传大文件，空间不足

## 7.功能特性:
- 模块化开发
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
- 默认 [col-md-12] 
```php
    //form 最简形式
    $form = $builder->form();
    //相当于 
    $form = $builder->row()->column(12)->form();

    //table 最简形式
    $table = $builder->table();
    //相当于 
    $table = $builder->row()->column(12)->table();
```
- 控制 [col-md-] 
```php
    //创建一个 row
    $row = $builder->row();
    //在这个row 里面显示两个 col-md-6
    $form = $row->column(6)->form();
    $table = $row->column(6)->table();
    //这个布局不太常用，一般一个都是单独的一个form或单独的table
    //参考后台的个人信息页面 [/admin/index/profile]，左边一个form修改[信息个人]，右边一个table显示[登录记录]
```
#### Row 配合 Column 使用

#### Tab : tab 切换，每个tab-content里面可以放 form 和 table等
###### 实例
```php
    // 获取一个tab
    $tab = $builder->tab();
    // 获取form
    $form1 = $tab->add('tab-from1')->form();
    $form1->formId('the-from' . 1);//重设formid 因为有多个form
    //
    $form2 = $tab->add('tab-from2')->form();
    $form2->formId('the-from' . 1);
    //
    $table = $tab->add('tab-table')->table();
    // 此例 或生成3个tab 两个form和一个table，他们之间相互独立
    // 参考后台的平台设置 [/admin/config/index]，多个配置表单，最好一个配置列表
```
#### Table 数据列表展示
###### 基础组件：同 from，只是不全部支持，考虑到显示效果，行内编辑的时候可用使用部分form组件
###### 实例
```php
    // 代码取后台管理员列表 [/admin/admin/index]

    // 代码比较原始，没有太多封装，后期再优化
    public function index()
    {
        $builder = Builder::getInstance('用户管理', '列表');

        $table = $builder->table();

        $form = $table->getSearch();
        $form->text('username', '账号', 3)->maxlength(20);
        $form->text('name', '姓名', 3)->maxlength(20);
        $form->text('phone', '手机号', 3)->maxlength(20);
        $form->text('email', '邮箱', 3)->maxlength(20);
        $form->select('role_id', '角色组', 3)->options($this->getRoleList());

        $table->show('id', 'ID');
        $table->show('username', '登录帐号');
        $table->text('name', '姓名')->autoPost()->getWapper()->addStyle('max-width:80px');
        $table->show('role_name', '角色');
        $table->show('group_name', '分组');
        $table->show('email', '电子邮箱')->default('无');
        $table->show('phone', '手机号')->default('无');
        $table->show('errors', '登录失败');
        $table->show('login_time', '登录时间')->getWapper()->addStyle('width:180px');
        $table->show('create_time', '添加时间')->getWapper()->addStyle('width:180px');

        $pagesize = 14;

        $page = input('__page__/d', 1);

        $page = $page < 1 ? 1 : $page;

        $searchData = request()->only([
            'username',
            'name',
            'email',
            'phone',
            'role_id',
        ], 'post');

        $where = [];
        if (!empty($searchData['username'])) {
            $where[] = ['username', 'like', '%' . $searchData['username'] . '%'];
        }

        if (!empty($searchData['name'])) {
            $where[] = ['name', 'like', '%' . $searchData['name'] . '%'];
        }

        if (!empty($searchData['phone'])) {
            $where[] = ['phone', 'like', '%' . $searchData['phone'] . '%'];
        }

        if (!empty($searchData['email'])) {
            $where[] = ['email', 'like', '%' . $searchData['email'] . '%'];
        }

        if (!empty($searchData['role_id'])) {
            $where[] = ['role_id', 'eq', $searchData['role_id']];
        }

        $sortOrder = input('__sort__', 'id desc');

        $data = $this->dataModel->where($where)->order($sortOrder)->limit(($page - 1) * $pagesize, $pagesize)->select();

        foreach ($data as &$d) {
            $d['__h_del__'] = $d['id'] == 1;
            $d['__h_en__'] = $d['enable'] == 1;
            $d['__h_dis__'] = $d['enable'] != 1 || $d['id'] == 1;
            $d['__h_clr__'] = $d['errors'] < 1;
        }

        unset($d);

        $table->data($data);
        $table->sortOrder($sortOrder);
        $table->paginator($this->dataModel->where($where)->count(), $pagesize);

        $table->getToolbar()
            ->btnAdd()
            ->btnEnable()
            ->btnDisable()
            ->btnDelete()
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit()
            ->btnEnable()
            ->btnDisable()
            ->btnDelete()
            ->btnPostRowid('clear_errors', url('clearErrors'), '', 'btn-info', 'mdi-backup-restore', 'title="重置登录失败次数"')
            ->mapClass([
                'delete' => ['hidden' => '__h_del__'],
                'enable' => ['hidden' => '__h_en__'],
                'disable' => ['hidden' => '__h_dis__'],
                'clear_errors' => ['hidden' => '__h_clr__'],
            ]);

        if (request()->isAjax()) {
            return $table->partial()->render();
        }

        return $builder->render();
    }
```
#### Form 数据列表展示
###### 基础组件：
> text ,checkbox ,radio ,button ,select ,multipleSelect ,textarea ,hidden ,color ,rangeSlider,
> file ,image ,date ,datetime ,time ,year ,month ,dateRange ,datetimeRange ,timeRange ,number
> switchBtn ,rate ,divider ,password ,decimal ,html ,raw ,show ,tags ,icon ,multipleImage ,multipleFile
> wangEditor ,tinymce ,ueditor ,editor ,ckeditor ,mdeditor ,match ,matches
###### 特殊 
> tab ,step

###### 实例
```php
    public function add()
    {
        if (request()->isPost()) {
            return $this->save();
        } else {
            return $this->form('添加');
        }
    }

    public function edit($id)
    {
        if (request()->isPost()) {
            return $this->save($id);
        } else {
            $data = $this->dataModel->get($id);
            if (!$data) {
                $this->error('数据不存在');
            }

            return $this->form('编辑', $data);
        }
    }

    private function form($title, $data = [])
    {
        $isEdit = isset($data['id']);

        $builder = Builder::getInstance('用户管理', $title);

        $form = $builder->form();

        $form->text('username', '登录帐号')->required()->beforSymbol('<i class="mdi mdi-account-key"></i>');
        $form->select('role_id', '角色组')->required()->options($this->getRoleList())
            ->disabled($isEdit && $data['id'] == 1);
        $form->password('password', '密码')->required(!$isEdit)->beforSymbol('<i class="mdi mdi-lock"></i>')
            ->help($isEdit ? '不修改则留空（6～20位）' : '添加用户，密码必填（6～20位）');
        $form->text('name', '姓名')->required()->beforSymbol('<i class="mdi mdi-rename-box"></i>');
        $form->image('avatar', '头像')->default('/assets/lightyearadmin/images/no-avatar.jpg');
        $form->text('email', '电子邮箱')->beforSymbol('<i class="mdi mdi-email-variant"></i>');
        $form->text('phone', '手机号')->beforSymbol('<i class="mdi mdi-cellphone-iphone"></i>');

        if ($isEdit) {

            $data['password'] = '';

            $form->show('create_time', '添加时间');
            $form->show('update_time', '修改时间');
        }

        $form->fill($data);

        return $builder->render();
    }

    private function save($id = 0)
    {
        //代码略 ....
        if (!$res) {
            $this->error('保存失败');
        }

        return Builder::getInstance()->layer()->closeRefresh(1, '保存成功');
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