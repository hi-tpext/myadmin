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

class EmptyData extends Model
{
    protected $name = 'empty_data';
    
    /**
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == '__not_found__') {
            return true;
        }
        if ($name == 'title' || $name == 'name' || $name == 'content' || $name == 'description') {
            return '无';
        }
        if ($name == 'url' || $name == 'link') {
            return '#';
        }
        if ($name = 'channel') {
            return $this;
        }
        if ($name == 'id' || $name = 'parent_id') {
            return 0;
        }

        return '__not_found__';
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        if ($name == '__not_found__') {
            return true;
        }
        if ($name == 'title' || $name == 'name' || $name == 'content' || $name == 'description') {
            return '无';
        }
        if ($name == 'url' || $name == 'link') {
            return '#';
        }
        if ($name = 'channel') {
            return $this;
        }
        if ($name == 'id' || $name = 'parent_id') {
            return 0;
        }

        return '__not_found__';
    }

    public function __toString(): string
    {
        return '<!--无数据-->';
    }

    public function __call($name, $arguments = [])
    {
        return $this;
    }
}
