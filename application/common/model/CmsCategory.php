<?php

namespace app\common\model;

use think\Model;

class CmsCategory extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeWrite(function ($data) {
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
        });

        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::where(['parent_id' => $data['parent_id']])->max('sort') + 5;
            }
        });

        self::afterDelete(function ($data) {
            static::where(['parent_id' => $data['id']])->update(['parent_id' => $data['parent_id']]);
            CmsContent::where(['category_id' => $data['id']])->update(['category_id' => $data['parent_id']]);
        });
    }

    public function buildList($parent = 0, $deep = 0)
    {
        $roots = static::where(['parent_id' => $parent])->order('sort')->select();
        $data = [];

        $deep += 1;

        foreach ($roots as $root) {

            if ($parent == 0) {
                $root['title_show'] = '├─' .$root['name'];
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

    public function getContentCountAttr($value, $data)
    {
        return CmsContent::where('category_id', $data['id'])->count();
    }
}
