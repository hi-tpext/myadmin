<?php

namespace app\common\model;

use think\Model;

class CmsBanner extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public function getPositionAttr($value, $data)
    {
        $position = CmsPosition::find($data['position_id']);
        return $position ? $position['name'] : '--';
    }

    public function position()
    {
        return $this->belongsTo(CmsPosition::class, 'position_id', 'id');
    }
}
