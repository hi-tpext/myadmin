<?php
namespace app\admin\controller;

use tpext\builder\common\Builder;
use tpext\builder\common\Wrapper;

class Form
{
    public function sumary()
    {
        $builder = Builder::getInstance('form', '全部组件');

        $form = $builder->form();

        $displayerMap = Wrapper::getDisplayerMap();

        foreach ($displayerMap as $name => $class) {
            if (in_array($name, ['fields', 'items', 'button', 'editor'])) {
                continue;
            }
            $field = $form->$name($name, $name)->default($class);
            if (method_exists($field, 'options')) {
                $field->options([1 => '今天', 2 => '明天', 3 => '后天', 4 => '后后天', 5 => '后后后天']);
                $field->value(mt_rand(1, 5));
            }
        }

        return $builder->render();
    }
}
