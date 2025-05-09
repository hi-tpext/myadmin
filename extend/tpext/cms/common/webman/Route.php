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

use Webman\Route as BaseRoute;

/**
 * for webman
 */
class Route extends BaseRoute
{
    /**
     * @param string $path
     * @param callable|mixed $callback
     * @return RouteObject
     */
    public static function get(string $path, $callback): RouteObject
    {
        return static::addRoute('GET', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable|mixed $callback
     * @return RouteObject
     */
    public static function post(string $path, $callback): RouteObject
    {
        return static::addRoute('POST', $path, $callback);
    }

    /**
     * @param array|string $methods
     * @param string $path
     * @param callable|mixed $callback
     * @return RouteObject
     */
    protected static function addRoute($methods, string $path, $callback): RouteObject
    {
        $path = rtrim($path, '$') . '[.html]';
        $path = str_replace('<id>', '{id:\d+}', $path);

        $route = new RouteObject($methods, static::$groupPrefix . $path, $callback);
        parent::$allRoutes[] = $route;

        if ($callback = parent::convertToCallable($path, $callback)) {
            parent::$collector->addRoute($methods, $path, ['callback' => $callback, 'route' => $route]);
        }
        if (parent::$instance) {
            parent::$instance->collect($route);
        }
        return $route;
    }
}
