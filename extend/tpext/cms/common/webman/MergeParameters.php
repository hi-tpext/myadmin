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

use Webman\MiddlewareInterface;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * 合并路由参数到GET参数
 */
class MergeParameters implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if ($request->route) {
            $params = $request->route->param();
            foreach ($params as $k => $v) {
                $request->setGet($k, $v);
            }
        }
        return $handler($request);
    }
}
