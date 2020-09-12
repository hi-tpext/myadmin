<?php
namespace app\admin\controller;

use tpext\builder\common\Builder;
use tpext\builder\common\Wrapper;

class Form
{
    public function sumary()
    {
        $builder = Builder::getInstance('form', 'all');

        $form = $builder->form();

        $displayerMap = Wrapper::getDisplayerMap();

        foreach ($displayerMap as $name => $class) {
            if (in_array($name, ['fields', 'items'])) {
                continue;
            }
            $field = $form->$name($name, $class);
            if (method_exists($field, 'options')) {
                $field->options([1 => '今天', 2 => '明天', 3 => '后台', 4 => '后后天', 5 => '后后后天']);
                $field->value(1);
            }
        }

        return $builder->render();
    }
}
