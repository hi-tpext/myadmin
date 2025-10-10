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

namespace tpext\cms\common\event;

use tpext\cms\common\model;
use think\Loader;

class ModelEvent
{
    public function watch()
    {
        $classMap = [
            model\CmsBanner::class,
            model\CmsChannel::class,
            model\CmsContent::class,
            model\CmsContentDetail::class,
            model\CmsContentField::class,
            model\CmsContentModel::class,
            model\CmsContentModelField::class,
            model\CmsContentPage::class,
            model\CmsPosition::class,
            model\CmsTag::class,
            model\CmsTemplate::class,
            model\CmsTemplateHtml::class
        ];

        $observe = [
            'before_write',
            'after_write',
            'before_insert',
            'after_insert',
            'before_update',
            'after_update',
            'before_delete',
            'after_delete',
            'before_restore',
            'after_restore'
        ];

        foreach ($classMap as $class) {
            foreach ($observe as $method) {
                $on = 'on' . ucfirst(Loader::parseName($method, 1));
                if (method_exists($class, $on)) {
                    $class::event($method, function ($data) use ($class, $on) {
                        $class::$on($data);
                    });
                }
            }
        }
    }
}
