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
use tpext\common\Tool;
use tpext\common\ExtLoader;
use tpext\cms\common\Module;

class CmsTemplate extends Model
{
    protected $name = 'cms_template';
    protected $autoWriteTimestamp = 'datetime';

    protected static function init()
    {
        /**是否为tp5**/
        if (method_exists(static::class, 'event')) {
            self::beforeInsert(function ($data) {
                return self::onBeforeInsert($data);
            });
            self::afterInsert(function ($data) {
                return self::onAfterInsert($data);
            });
            self::afterUpdate(function ($data) {
                return self::onAfterUpdate($data);
            });
            self::afterDelete(function ($data) {
                return self::onAfterDelete($data);
            });
        }
    }

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onAfterInsert($data)
    {
        ExtLoader::trigger('cms_template_on_after_insert', $data);
    }

    public static function onAfterUpdate($data)
    {
        if (!isset($data['id'])) {
            return;
        }

        cache('cms_template_' . $data['id'], null);

        ExtLoader::trigger('cms_template_on_after_update', $data);
    }

    public static function onAfterDelete($data)
    {
        cache('cms_template_' . $data['id'], null);

        ExtLoader::trigger('cms_template_on_after_delete', $data);

        CmsTemplateHtml::where(['template_id' => $data['id']])->delete();
        CmsContentPage::where(['template_id' => $data['id']])->delete();
    }

    public function getPagesCountAttr($value, $data)
    {
        return CmsTemplateHtml::where('template_id', $data['id'])->count();
    }

    public static function initPath($view_path)
    {
        try {
            $rootPath = Module::getInstance()->getRoot();
            $newTpl = self::getNewTpl();

            if (!is_dir($view_path . '/channel')) {
                if (mkdir($view_path . '/channel', 0775, true)) {
                    $text = self::getTemplatePart('tpl/channel.html');
                    file_put_contents($view_path . '/channel/default.html', str_replace('<!--__content__-->', $text, $newTpl));
                }
            }
            if (!is_dir($view_path . '/content')) {
                if (mkdir($view_path . '/content', 0775, true)) {
                    $text = self::getTemplatePart('tpl/content.html');
                    file_put_contents($view_path . '/content/default.html', str_replace('<!--__content__-->', $text, $newTpl));
                }
            }
            if (!is_dir($view_path . '/static')) {
                Tool::copyDir($rootPath . 'tpl/css', $view_path . '/static/css');
                Tool::copyDir($rootPath . 'tpl/js', $view_path . '/static/js');
                Tool::copyDir($rootPath . 'tpl/images', $view_path . '/static/images');
            }
            if (!is_dir($view_path . '/common')) {
                if (mkdir($view_path . '/common', 0775, true)) {
                    file_put_contents($view_path . '/common/header.html', file_get_contents($rootPath . 'tpl/header.html'));
                    file_put_contents($view_path . '/common/navi.html', file_get_contents($rootPath . 'tpl/navi.html'));
                    file_put_contents($view_path . '/common/footer.html', file_get_contents($rootPath . 'tpl/footer.html'));
                    file_put_contents($view_path . '/common/layout.html', file_get_contents($rootPath . 'tpl/layout.html'));
                }
            }
            if (!is_dir($view_path . '/dynamic')) {
                if (mkdir($view_path . '/dynamic', 0775, true)) {
                    $text = self::getTemplatePart('tpl/tag.html');
                    file_put_contents($view_path . '/dynamic/tag.html', str_replace('<!--__content__-->', $text, $newTpl));

                    $text = self::getTemplatePart('tpl/search.html');
                    file_put_contents($view_path . '/dynamic/search.html', str_replace('<!--__content__-->', $text, $newTpl));

                    $text = self::getTemplatePart('tpl/demo.html');
                    file_put_contents($view_path . '/dynamic/demo.html', str_replace('<!--__content__-->', $text, $newTpl));
                }
            }
            if (!is_file($view_path . '/index.html')) {
                $text = self::getTemplatePart('tpl/index.html');
                $sliderScript = self::getTemplatePart('tpl/slider.html');
                $sliderCss = '<link href="./static/css/slider.css" rel="stylesheet" />';

                file_put_contents($view_path . '/index.html', str_replace(['<!--__content__-->', '<!--__script__-->', '<!--__style__-->'], [$text, $sliderScript, $sliderCss], $newTpl));

                if (!is_file($view_path . '/about.html')) {
                    $text = self::getTemplatePart('tpl/single.html');
                    file_put_contents($view_path . '/about.html', str_replace('<!--__content__-->', $text, $newTpl));
                }
                if (!is_file($view_path . '/contact.html')) {
                    $text = self::getTemplatePart('tpl/single.html') . PHP_EOL . self::getTemplatePart('tpl/map.html');
                    file_put_contents($view_path . '/contact.html', str_replace('<!--__content__-->', $text, $newTpl));
                }
            }
        }
        catch (\Throwable $e) {
            trace('initPath error:'. $e->__tostring(), 'error');
        }
    }

    /**
     * @return bool|string
     */
    public static function getNewTpl()
    {
        $rootPath = Module::getInstance()->getRoot();
        $newTpl = file_get_contents($rootPath . (Module::getInstance()->config('use_layout', 1) == 1 ? 'tpl/new1.html' : 'tpl/new0.html'));

        return $newTpl;
    }

    /**
     * 获取模板内容
     * 
     * @param string $tplPath
     * @return string
     */
    public static function getTemplatePart($tplPath)
    {
        $text = file_get_contents(Module::getInstance()->getRoot() . $tplPath);
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/\n{2,}/s', "\n", $text);
        $lines = explode("\n", $text);
        return implode(PHP_EOL . (Module::getInstance()->config('use_layout', 1) == 1 ? '    ' : '        '), $lines);
    }
}
