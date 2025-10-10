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
use tpext\common\ExtLoader;

class CmsBanner extends Model
{
    protected $name = 'cms_banner';

    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onAfterInsert($data)
    {
        ExtLoader::trigger('cms_banner_on_after_insert', $data);
    }

    public static function onAfterUpdate($data)
    {
        if (!isset($data['id'])) {
            return;
        }
        Cache::delete('cms_banner_' . $data['id']);

        ExtLoader::trigger('cms_banner_on_after_update', $data);
    }

    public static function onAfterDelete($data)
    {
        Cache::delete('cms_banner_' . $data['id']);
        ExtLoader::trigger('cms_banner_on_after_delete', $data);
    }

    public function position()
    {
        return $this->belongsTo(CmsPosition::class, 'position_id', 'id');
    }
}
