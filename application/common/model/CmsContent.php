<?php

namespace app\common\model;

use think\Model;

class CmsContent extends Model
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

    public function category()
    {
        return $this->belongsTo(CmsCategory::class, 'id', 'category_id');
    }

    public function getAttrAttr($value, $data)
    {
        $attr = [];
        if ($data['is_recommend']) {
            $attr[] = 'is_recommend';
        }
        if ($data['is_hot']) {
            $attr[] = 'is_hot';
        }
        if ($data['is_top']) {
            $attr[] = 'is_top';
        }

        return $attr;
    }

    public function setTagsAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return is_array($value) ? ',' . implode(',', $value) . ',' : ',' . trim($value, ',') . ',';
    }
}
