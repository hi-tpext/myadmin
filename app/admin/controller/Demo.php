<?php
namespace app\admin\controller;

use think\Controller;
use tpext\builder\common\Builder;

class Demo extends Controller
{
    public function demo1()
    {
        $builder = Builder::getInstance('builer', 'form');
        $form = $builder->form(); //获取一个表单 实例
        $form->text('name', '姓名')->required();
        $form->number('age', '年龄')->min(18)->required();
        $form->radio('gender', '性别')->options([1 => '男', 2 => '女'])->default(1);
        $form->switchBtn('status', '状态');
        $form->textarea('about', '简介')->maxlength(500);

        //设置提交地址，同控制器的action:demo1Post()，不填则提交到相同action:demo1,可根据是否为为ajax判断展示数据还是保存数据
        $form->action(url('demo1Post'));

        //数据填充，一般为数据库查询的一条记录
        $data = ['name' => '', 'age' => 6, 'gender' => 1, 'status' => 1, 'about' => '我叫小明'];

        $form->fill($data);

        return $builder->render();
    }

    public function demo2()
    {
        $builder = Builder::getInstance('builer', 'table');
        $table = $builder->table(); //获取一个表格 实例

        $search = $table->getSearch();
        $search->tabLink('gender')->options([1 => '男', 2 => '女']);
        $search->select('gender', '性别')->options([1 => '男', 2 => '女']);

        $table->text('name', '姓名')->autoPost()->getWrapper()->addStyle('width:140px;'); //限制列宽度
        $table->show('age', '年龄');
        $table->match('gender', '性别')->options([1 => '男', 2 => '女'])->mapClassWhen(1, 'success')->mapClassWhen(2, 'info');
        $table->switchBtn('status', '状态')->autoPost();
        $table->show('about', '简介')->cut(50)->getWrapper()->addStyle('width:30%;');

        $data = $this->getData();
        $table->fill($data);

        $table->paginator(count($data), 12);

        if (request()->isAjax()) {
            return $table->partial()->render();
        }

        return $builder->render();
    }

    /**
     * 生成模拟数据
     *
     * @return array
     */
    private function getData()
    {
        //数据填充，一般为数据库查询的多条记录
        $data = [];

        $names = ['小明', '小红', '小刚', '小芳'];
        for ($i = 0; $i < 12; $i += 1) {
            $name = $names[mt_rand(0, count($names) - 1)];
            $data[] = ['name' => $name, 'age' => mt_rand(15, 25), 'gender' => mt_rand(1, 2), 'status' => 1, 'about' => str_repeat('我叫' . $name, mt_rand(3, 30))];
        }

        return $data;
    }

    /**
     * 生成模拟树形数据
     *
     * @return array
     */
    private function getTree()
    {
        return [
            ['name' => '10~12', 'id' => 1, 'parent_id' => 0],
            ['name' => '12~16', 'id' => 2, 'parent_id' => 0],
            ['name' => '16~20', 'id' => 3, 'parent_id' => 0],
            ['name' => '20~22', 'id' => 4, 'parent_id' => 0],
            ['name' => '22~24', 'id' => 5, 'parent_id' => 0],
            //
            ['name' => '16', 'id' => 6, 'parent_id' => 3],
            ['name' => '17', 'id' => 7, 'parent_id' => 3],
            ['name' => '18', 'id' => 8, 'parent_id' => 3],
            ['name' => '19', 'id' => 9, 'parent_id' => 3],
            ['name' => '20', 'id' => 10, 'parent_id' => 3],
            //
            ['name' => '22', 'id' => 11, 'parent_id' => 5],
            ['name' => '23', 'id' => 12, 'parent_id' => 5],
        ];
    }

    public function demo3()
    {
        $builder = Builder::getInstance('builer', 'content');
        $builder->content(4)->display('<div style="widht:100%;height:30px;margin-top:10px;border:1px dashed red;">{$name}</div>', ['name' => 'col-md-4']);
        //新的一个row,所以这个col-md-6不会跟随上面一个
        $builder->row()->column(6)->content()->display('<div style="widht:100%;height:30px;margin:20px 0;border:1px dashed red;">col-md-6，时间' . date('Y-m-d H:i:s') . '</div>');

        $builder->row();

        //树形结构，col-md-n 控制宽带在这里是不灵活的，让它变成`col-md-0 left-tree`。然后给`left-tree`定义宽度。
        $tree = $builder->tree('0 left-tree');
        $right = $builder->column('0 right-list')->row();

        //$row->column(4)->content() 跟 $row->content(4)等效。table,form也类似
        $right->column(4)->content()->fetch(); //模板路径规则跟tp框架一致。留空模板名就是当前action`admin/view/demo/demo3.html`
        $right->content(3)->fetch('demo33', ['name' => 'col-md-4', 'data' => json_encode(['colors' => ['红色', '蓝色', '橙色'], 'datas' => [300, 50, 100]])]); //另外指定模板`admin/view/demo/demo33.html`

        $table = $right->table();
        $sarch = $table->getSearch();

        $treeData = $this->getTree();
        $tree->fill($treeData);

        $sarch->select('category_id', '分类')->optionsData($treeData); //这个搜索字段用来接收树形结构被点击的值。
        $sarch->text('name', '姓名');

        $tree->trigger('.row-category_id'); //绑定触发

        //
        $data = $this->getData();
        $table->fill($data);

        $builder->addStyleSheet('
            .left-tree
            {
                width:11%;
                float:left;
            }

            .right-list
            {
                width:88%;
                float:right;
            }
        ');

        //表格ajax的时候，只会替换table部分，若需要实时更新的数据，可使用addTop\addBottom。

        $top = $table->addTop();
        $top->addStyle('padding:20px');

        $top->content(12)->display('<label class="text-center label label-default">搜索:' . json_encode(input('post.')) . '</label>');

        $top->content(3)->display('<p>这里是Bottom left，时间' . date('Y-m-d H:i:s') . '</p>');
        $top->content(3)->display('<p>这里是Bottom right</p>');
        $top->content(4)->fetch('demo333', ['data' => json_encode(['months' => ["一月", "二月", "三月", "四月", "五月", "六月", "七月"], 'in' => [mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100)], 'out' => [mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100), mt_rand(50, 100)]])]);
        $row = $top->column(12)->row();

        $row->content(6)->display('<p>这里是新的 一个 row</p>');

        if (request()->isAjax()) {
            //addBottom/addTop类似，只是一个在表格上面，一个在下面
            $table->addBottom()->content(6)->display('<script>layer && layer.msg("搜索:category_id-' . input('post.category_id') . '")</script>'); //可以附加js,在每次表格刷新是做点什么
            return $table->partial()->render();
        }

        return $builder->render();
    }

    public function demo4()
    {
        $builder = Builder::getInstance('builer', 'display');

        $builder->content()->display('<div style="widht:100%;margin-top:10px;border:1px dashed red;">{$desc}<img src="https://gitee.com/ichynul/myadmin/widgets/widget_card.svg?colors=393222,ebdfc1,fffae5,d8ca9f,393222,a28b40" ></div>', ['desc' => '$builder->content->display(\'\')的内容']);

        $form = $builder->form();

        $form->text('name', '姓名');
        $form->html('demo', 'Demo')->display('<div style="widht:100%;margin-top:10px;border:1px dashed blue;">{$desc}</div>年龄：<input class="form-control" name="age" value="18" />', ['desc' => '$form->html()->display(\'\')的内容']);
        $form->textarea('about', '简介');
        $form->action(url('demo1Post'));

        return $builder->render();
    }

    public function demo5()
    {
        $builder = Builder::getInstance('builer', 'fetch');

        $builder->content()->fetch('/demo/demo3');

        $form = $builder->form();

        $form->text('name', '姓名');
        $form->html('demo', 'Demo')->fetch('/demo/demo33', ['name' => '$form->html(\'demo\', \'Demo\')->fetch(\'/demo/demo33\');', 'data' => json_encode(['colors' => ['红色', '蓝色', '橙色'], 'datas' => [300, 50, 100]])]);
        $form->textarea('about', '简介');
        $form->action(url('demo1Post'));

        return $builder->render();
    }

    public function add()
    {
        return $this->demo1();
    }

    public function edit()
    {
        return $this->demo1();
    }

    public function demo1Post()
    {
        //$data = request()->post();

        //限制提交字段
        $data = request()->only([
            'name',
            'age',
            'gender',
            'status',
            'about',
        ], 'post');

        //数据验证，required()仅前端显示红*、js验证，后端仍需验证一次
        $result = $this->validate($data, [
            'name|姓名' => 'require',
            'age|年龄' => 'require|number|egt:18|lt:100',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($data['name'] == 'hello') {
            //错误提示，可以附加js，成功或失败都可附加js，以<script></script>包围
            $this->error('world', '', ['script' => '<script>alert("这是一个js,页码将刷新");location.reload()</script>']);
        }

        //保存数据，略

        $this->success('提交成功' . json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function autoPost()
    {
        $this->success('修改成功');
    }

    public function delete()
    {
        return mt_rand(0, 1) ? json(['code' => 0, '删除失败']) : json(['code' => 1, '删除成功']);
    }
}
