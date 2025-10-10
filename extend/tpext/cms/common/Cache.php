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

namespace tpext\cms\common;

use think\facade\Cache as ThinkCache;
use tpext\common\ExtLoader;

class Cache extends ThinkCache
{
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public static function delete($name): bool
    {
        if (ExtLoader::isTP51()) {
            return parent::rm($name);
        } else {
            return parent::delete($name);
        }
    }

    /**
     * 删除指定标签的缓存
     * @access public
     * @param string $tag
     * @return bool
     */
    public static function deleteTag($tag): bool
    {
        if (ExtLoader::isTP51()) {
            return parent::clear($tag);
        } else {
            return parent::tag($tag)->clear();
        }
    }
}
