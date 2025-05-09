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

namespace tpext\cms\common\webman;

use Webman\Route\Route as BaseRouteObject;

/**
 * for webman
 */
class RouteObject extends BaseRouteObject
{
    /**
     * Summary of append
     * @param array $params
     * @return static
     */
    public function append($params)
    {
        $this->params = array_merge($this->params, $params);
        $this->middleware(MergeParameters::class);

        return $this;
    }

    /**
     * Summary of pattern
     * @param array $params
     * @return static
     */
    public function pattern($params)
    {
        return $this;
    }

    /**
     * Summary of ajax
     * @return static
     */
    public function ajax()
    {
        return $this;
    }
}
