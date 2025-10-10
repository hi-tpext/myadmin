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

class CmsTag extends Model
{
    protected $name = 'cms_tag';
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onAfterUpdate($data)
    {
        if (!isset($data['id'])) {
            return;
        }
        Cache::delete('cms_tag_' . $data['id']);
    }

    public static function onAfterDelete($data)
    {
        Cache::delete('cms_tag_' . $data['id']);
    }
}
