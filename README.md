# 欢迎使用 

## 1.安装:**
### 全新安装
- thinkphp 5.1.*
```bash
composer create-project topthink/think=5.1.* myadmin
```
- 后台扩展

```bash
composer require ichynul/tpextmyadmin>=1.1
```
### 更新安装
- 在现有基础上更新
```bash
composer update
```

## 2.配置:
- 正确配置 database数据库

## 3.扩展安装:
- 打开 `http://www.yourhost.com/admin/tpext/index`
- 安装 `[tpext.core]`,
- 安装其余装扩展

## 4.登录后台:
- 打开 `http://www.yourhost.com/admin` ，会跳转登录 默认账号 admin tpextadmin


## 功能特性:
- 模块化开发
**扩展依赖**
[tpextbuilder]<https://gitee.com/ichynul/tpextbuilder>
[lightyearadmin]<https://gitee.com/ichynul/lightyearadmin>
[tpextmyadmin]<https://gitee.com/ichynul/tpextmyadmin>

- 基于 [bootstrap]和[Light-Year-Admin-Template]的后台模板， 封装了大部分常用组件。
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
    // 代码取后台管理员列表 [/admin/config/index]

    // 代码比较原始，没有太多封装，后期再优化
    public function index()
    {
        $builder = Builder::getInstance('用户管理', '列表');

        $form = $builder->form();//搜索表单

        $form->text('username', '账号', 3)->maxlength(20);
        $form->text('name', '姓名', 3)->maxlength(20);
        $form->text('phone', '手机号', 3)->maxlength(20);
        $form->text('email', '邮箱', 3)->maxlength(20);
        // getRoleList 获取角色列表，代码不列出来了
        $form->select('role_id', '角色组', 3)->options($this->getRoleList());

        $table = $builder->table();//列表

        $table->searchForm($form);//绑定搜索

        $table->show('id', 'ID');//多大用show
        $table->show('username', '登录帐号');
        //支持行内编辑 [text,textarea,select,radio,checkbox]，其他组件应该也可以效果不太好，
        //autoPost($url) 输入框失去焦点后自动提交。
        //getWapper() addStyle addClass 控制td样式及class
        $table->text('name', '姓名')->autoPost()->getWapper()->addStyle('max-width:80px');
        $table->show('role_name', '角色');
        $table->show('email', '电子邮箱')->default('无');
        $table->show('phone', '手机号')->default('无');
        $table->show('errors', '登录失败');
        $table->show('create_time', '添加时间')->getWapper()->addStyle('width:180px');
        $table->show('update_time', '修改时间')->getWapper()->addStyle('width:180px');

        $pagezise = 10;//每页显示

        $page = input('__page__/d', 1);//获取当前页码

        $page = $page < 1 ? 1 : $page;

        //搜索数据
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

        //排序字段，默认为id ，可用 $table->sortable(['feild1','feild2'])控制哪些字段可排序
        $sortOrder = 'id asc';

        $sort = input('__sort__');
        if ($sort) {
            $arr = explode(':', $sort);
            if (count($arr) == 2) {
                $sortOrder = implode(' ', $arr);
            }
        }

        $data = $this->dataModel->where($where)->order($sortOrder)
            ->limit(($page - 1) * $pagezise, $pagezise)->select();

        foreach ($data as &$d) {
            $d['__h_del__'] = $d['id'] == 1;// 用户id 为1是超级管理员
            $d['__h_en__'] = $d['enable'] == 1; 
            $d['__h_dis__'] = $d['enable'] != 1 || $d['id'] == 1;
        }

        $table->data($data);
        $table->paginator($this->dataModel->where($where)->count(), $pagezise);//分页设置

        //控制哪些tollbar显示，及显示文字，默认 [btnAdd btnDelete]
        // $table->useToolbar(false);//禁用
        $table->getToolbar()
            ->btnAdd()
            ->btnEnable()
            ->btnDisable()
            ->btnDelete()
            ->btnRefresh();
        //控制操作按钮,及显示文字, 默认 [btnEdit btnDelete]
        //$table->useActionbar(false);//禁用
        $table->getActionbar()
            ->btnEdit()
            ->btnEnable()
            ->btnDisable()
            ->btnDelete()
            ->mapClass([
                'delete' => ['hidden' => '__h_del__'], //控制按钮的class [hidden，disabled]
                'enable' => ['hidden' => '__h_en__'],
                'disable' => ['hidden' => '__h_dis__'],
            ]);

        if (request()->isAjax()) {//ajax 一般是翻页、搜索，只返回表格部分替换
            return $table->partial()->render();
        }

        return $builder->render();//整个页面输出
    }
```
#### Form 数据列表展示
###### 基础组件：
- text,checkbox,radio,button,select,multipleSelect,textarea,hidden,color,rangeSlider,
- file,image,date,datetime,time,year,month,dateRange,datetimeRange,timeRange,number
- switchBtn,rate,divider,password,decimal,html,raw,show,tags,icon,multipleImage,multipleFile
- wangEditor,tinymce,ueditor,editor,ckeditor,mdeditor,match,matches
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
## 6.效果展示:

[![](https://gitee.com/ichynul/myadmin/raw/master/images/1.png)](https://gitee.com/ichynul/myadmin/raw/master/images/1.png "1")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/2.png)](https://gitee.com/ichynul/myadmin/raw/master/images/2.png "2")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/3.png)](https://gitee.com/ichynul/myadmin/raw/master/images/3.png "3")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/4.png)](https://gitee.com/ichynul/myadmin/raw/master/images/4.png "4")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/5.png)](https://gitee.com/ichynul/myadmin/raw/master/images/5.png "5")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/6.png)](https://gitee.com/ichynul/myadmin/raw/master/images/6.png "6")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/7.png)](https://gitee.com/ichynul/myadmin/raw/master/images/7.png "7")
[![](https://gitee.com/ichynul/myadmin/raw/master/images/8.png)](https://gitee.com/ichynul/myadmin/raw/master/images/8.png "8")