<?php

namespace app\common\model;

use think\Model;
use tpext\builder\traits\TreeModel;

class CmsCategory extends Model
{
    use TreeModel;

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

    protected function treeInit()
    {
        $this->treeTextField = 'name';
        $this->treeSortField = 'sort';
    }

    public function getContentCountAttr($value, $data)
    {
        return CmsContent::where('category_id', $data['id'])->count();
    }
}
