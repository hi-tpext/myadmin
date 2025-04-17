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

class CmsContentModel extends Model
{
    protected $name = 'cms_content_model';

    protected $autoWriteTimestamp = 'datetime';

    protected static function init()
    {
        /**是否为tp5**/
        if (method_exists(static::class, 'event')) {
            self::beforeInsert(function ($data) {
                return self::onBeforeInsert($data);
            });
            self::afterWrite(function ($data) {
                return self::onAfterWrite($data);
            });
            self::afterDelete(function ($data) {
                return self::onAfterDelete($data);
            });
        }
    }

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
        cache('cms_content_model_fields_' . $data['id'], null);
        cache('cms_content_model_fields_main_left_' . $data['id'], null);
        cache('cms_content_model_fields_main_right_' . $data['id'], null);
        cache('cms_content_model_fields_extend_' . $data['id'], null);
    }

    public static function onAfterDelete($data)
    {
        cache('cms_content_model_fields_' . $data['id'], null);
        cache('cms_content_model_fields_main_left_' . $data['id'], null);
        cache('cms_content_model_fields_main_right_' . $data['id'], null);
        cache('cms_content_model_fields_extend_' . $data['id'], null);
    }

    public function setFieldsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}
