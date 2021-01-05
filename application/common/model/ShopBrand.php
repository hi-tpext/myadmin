<?php

namespace app\common\model;

use think\Model;
use tpext\builder\traits\TreeModel;

class ShopBrand extends Model
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
            ShopGoods::where(['brand_id' => $data['id']])->update(['brand_id' => $data['parent_id']]);
        });
    }

    public function getGoodsCountAttr($value, $data)
    {
        return ShopGoods::where('brand_id', $data['id'])->count();
    }

    protected function treeInit()
    {
        $this->treeTextField = 'name';
        $this->treeSortField = 'sort';
    }
}
