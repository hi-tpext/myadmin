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
use tpext\common\ExtLoader;

class CmsContentField extends Model
{
    protected $name = 'cms_content_field';

    protected $autoWriteTimestamp = 'datetime';

    public static $FIELD_TYPES = [
        'tinyint' => 'tinyint(数字3位)',
        'int' => 'int(数字10位)',
        'bigint' => 'bigint(数字20位)',
        'float' => 'float(小数)',
        'boolean' => 'boolean(0或1)',
        'date' => 'date(日期)',
        'datetime' => 'datetime(日期时间)',
        'varchar' => 'varchar(不定长文本)',
        'text' => 'text(长文本)',
    ];

    public static $displayerTypes = [
        'text' => '单行文本',
        'textarea' => '多行文本',
        'radio' => '单选框',
        'checkbox' => '多选框',
        'select' => '下拉单选框',
        'multipleSelect' => '下拉多选框',
        'number' => '数字录入',
        'float' => '小数录入',
        'switchBtn' => '开关按钮',
        'yesOrNo' => '是或否',
        'editor' => '富文本编辑器',
        'image' => '单图片上传',
        'images' => '多图片上传',
        'file' => '单文件上传',
        'files' => '多文件上传',
        'date' => '日期',
        'datetime' => '日期时间',
        'color' => '颜色选择器',
        'icon' => '图标选择器',
        'none' => '不需要录入'
    ];

    protected static function init()
    {
        if (method_exists(static::class, 'event')) {
            self::afterDelete(function ($data) {
                return self::onAfterDelete($data);
            });
        }
    }

    public static function onAfterDelete($data)
    {
        CmsContentModelField::where('name', $data['name'])->delete();
        ExtLoader::trigger('cms_content_field_on_after_delete', $data);
    }
}
