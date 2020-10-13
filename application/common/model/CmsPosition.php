<?php

namespace app\common\model;

use think\Model;

class CmsPosition extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::max('sort') + 5;
            }
        });

        self::afterDelete(function ($data) {
            CmsBanner::where(['position_id' => $data['id']])->update(['position_id' => 0]);
        });
    }

    public function getBannerCountAttr($value, $data)
    {
        return CmsBanner::where('position_id', $data['id'])->count();
    }
}
