<?php

namespace app\common\model;

use think\Model;

class ShopBrand extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::where(['parent_id' => $data['parent_id']])->max('sort') + 5;
        }
    }

    public static function onBeforeWrite($data)
    {
        if (isset($data['parent_id'])) {
            if ($data['parent_id'] == 0) {
                $data['deep'] = 1;
                $data['path'] = ',';
            } else {
                $parent = static::get($data['parent_id']);
                if ($parent) {
                    $data['deep'] = $parent['deep'] + 1;
                    $data['path'] = $parent['path'] . $data['parent_id'] . ',';
                }
            }
        }
    }

    public static function onAfterDelete($data)
    {
        static::where(['parent_id' => $data['id']])->update(['parent_id' => $data['parent_id']]);
        CmsContent::where(['brand_id' => $data['id']])->update(['brand_id' => $data['parent_id']]);
    }

    public function getGoodsCountAttr($value, $data)
    {
        return ShopGoods::where('brand_id', $data['id'])->count();
    }

    public function buildList($parent = 0, $deep = 0)
    {
        $roots = static::where(['parent_id' => $parent])->order('sort')->select();
        $data = [];

        $deep += 1;

        foreach ($roots as $root) {

            if ($parent == 0) {
                $root['title_show'] = '├─' . $root['name'];
            } else {
                $root['title_show'] = str_repeat('&nbsp;', ($deep - 1) * 6) . '├─' . $root['name'];
            }

            $data[] = $root;

            $data = array_merge($data, $this->buildList($root['id'], $deep));
        }

        return $data;
    }

    public function buildTree($parent = 0, $deep = 0, $except = 0)
    {
        $roots = static::where(['parent_id' => $parent])->order('sort')->field('id,name,parent_id')->select();
        $data = [];

        $deep += 1;

        foreach ($roots as $root) {

            $root['title_show'] = '|' . str_repeat('──', $deep) . $root['name'];

            if ($root['id'] == $except) {
                continue;
            }

            $root['title_show'];

            $data[$root['id']] = $root['title_show'];

            $data += $this->buildTree($root['id'], $deep, $except);
        }

        return $data;
    }
}
