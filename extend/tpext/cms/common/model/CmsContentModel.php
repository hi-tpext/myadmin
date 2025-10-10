<?php
// +----------------------------------------------------------------------
// | tpext.cms
// +----------------------------------------------------------------------
// | Copyright (c) tpext.cms All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lhy <ichynul@163.com>
// +----------------------------------------------------------------------

namespace tpext\cms\common\model;

use think\Model;
use tpext\cms\common\Cache;

class CmsContentModel extends Model
{
    protected $name = 'cms_content_model';

    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 1;
        }
    }

    public static function onAfterWrite($data)
    {
        if (!isset($data['id'])) {
            return;
        }
        Cache::delete('cms_content_model_fields_' . $data['id']);
        Cache::delete('cms_content_model_fields_main_left_' . $data['id']);
        Cache::delete('cms_content_model_fields_main_right_' . $data['id']);
        Cache::delete('cms_content_model_fields_extend_' . $data['id']);
    }

    public static function onAfterDelete($data)
    {
        Cache::delete('cms_content_model_fields_' . $data['id']);
        Cache::delete('cms_content_model_fields_main_left_' . $data['id']);
        Cache::delete('cms_content_model_fields_main_right_' . $data['id']);
        Cache::delete('cms_content_model_fields_extend_' . $data['id']);
    }

    public function setFieldsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}
