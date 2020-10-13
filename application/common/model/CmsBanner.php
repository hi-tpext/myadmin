<?php

namespace app\common\model;

use think\Model;

class CmsBanner extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::max('sort') + 5;
            }
        });
    }

    public function getPositionAttr($value, $data)
    {
        $position = CmsPosition::get($data['position_id']);
        return $position ? $position['name'] : '--';
    }
}
