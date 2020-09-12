<?php
namespace app\admin\controller;

use think\Controller;
use tpext\builder\common\Builder as Bd;

class Builder extends Controller
{
    public function sumary()
    {
        $builder = Bd::getInstance('Builder', '总览');

        $form = $builder->form();

        $demo1 = <<<EOT
```php
<?php
namespace app\admin\controller;

/**引入Builder**/
use tpext\builder\common\Builder；
use think\Controller;

class Demo extends Controller
{
    public function demo1()
    {
        \$builder = Builder::getInstance('标题', '描述');
        \$form = \$builder->form(); //获取一个表单 实例
        \$form->text('name', '姓名')->required();
        \$form->number('age', '年龄')->min(18)->required();
        \$form->radio('gender', '性别')->options([1 => '男', 2 => '女'])->default(1);
        \$form->switchBtn('status', '状态');
        \$form->textarea('about', '简介')->maxlength(500);

        //设置提交地址，同控制器的action:demo1Post()，不填则提交到相同action:demo1(),可根据是否为为ajax判断展示数据还是保存数据
        \$form->action(url('demo1Post'));

        //数据填充，一般为数据库查询的一条记录
        \$data = ['name' => '', 'age' => 6, 'gender' => 1, 'status' => 1, 'about' => '我叫小明'];

        \$form->fill(\$data);

        return \$builder->render();
    }
｝
```

EOT;

        $form->mdreader('表格[Form]')->value($demo1)->size(12, 12); //markdown 展示
        $form->raw('demo1', ' ')->showLabel(false)->value('<a onclick="top.$.fn.multitabs().create(this, true);return false;" href="' . url('demo/demo1') . '">点此查看</a>表单实例');

        $demo2 = <<<EOT
```php
<?php
namespace app\admin\controller;

use tpext\builder\common\Builder；
use think\Controller;

class Demo extends Controller
{
    public function demo2()
    {
        \$builder = Builder::getInstance('builer', 'table');
        \$table = \$builder->table(); //获取一个表格 实例
        \$table->text('name', '姓名')->autoPost()->getWrapper()->addStyle('width:140px;');//限制列宽度
        \$table->show('age', '年龄');
        \$table->match('gender', '性别')->options([1 => '男', 2 => '女'])->mapClassWhen(1, 'success')->mapClassWhen(2, 'info');
        \$table->switchBtn('status', '状态')->autoPost();
        \$table->show('about', '简介')->cut(50)->getWrapper()->addStyle('width:30%;');

        //数据填充，一般为数据库查询的多条记录
        \$data = [];

        \$names = ['小明', '小红', '小刚', '小芳'];
        for (\$i = 0; \$i < 12; \$i += 1) {
            \$name = \$names[mt_rand(0, count(\$names) - 1)];
            \$data[] = ['name' => \$name, 'age' => mt_rand(15, 25), 'gender' => mt_rand(0, 1), 'status' => 1, 'about' => str_repeat('我叫' . \$name, mt_rand(3, 30))];
        }

        \$table->fill(\$data);

        \$table->paginator(count(\$data), 12);

        if (request()->isAjax()) {
            return \$table->partial()->render();
        }

        return \$builder->render();
    }
｝
```

EOT;
        $form->mdreader('表格[Table]')->value($demo2)->size(12, 12);
        $form->raw('demo2', ' ')->showLabel(false)->value('<a onclick="top.$.fn.multitabs().create(this, true);return false;" href="' . url('demo/demo2') . '">点此查看</a>表格实例');

        $form->bottomButtons(false); //仅显示，不需要表单提交按钮

        return $builder->render();
    }

    public function useContent()
    {
        $builder = Bd::getInstance('Builder', '');
        return $builder->content()->display('builder 布局');
    }

    public function useDisplay()
    {

    }

    public function useFetch()
    {

    }
}
