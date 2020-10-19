<?php

namespace app\common\model;

use think\Model;

class CmsPosition extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onAfterDelete($data)
    {
        CmsBanner::where(['position_id' => $data['id']])->update(['position_id' => 0]);
    }

    public function getBannerCountAttr($value, $data)
    {
        return CmsBanner::where('position_id', $data['id'])->count();
    }
}
