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

namespace tpext\cms\common\taglib;

use tpext\cms\common\model\EmptyData;
use tpext\think\App;

class Processer
{
    protected static $path = '';
    protected static $isAdmin = false;

    public static function setPath($val = '')
    {
        static::$path = $val;
    }

    public static function getPath()
    {
        return static::$path;
    }

    public static function setIsAdmin($val = true)
    {
        static::$isAdmin = $val;
    }

    public static function getDbNamespace()
    {
        return class_exists('\think\facade\Db') ? '\think\facade\Db' : '\think\Db';
    }

    /**
     * 替换栏目路径
     * 
     * @param array|mixed $channel
     * @return string
     */
    public static function resolveChannelPath($channel)
    {
        return 'channel/' . str_replace('[id]', $channel['id'], ltrim($channel['channel_path'], '/'));
    }

    /**
     * 替换内容路径
     * 
     * @param array|mixed $content
     * @param array|mixed $channel
     * @return string
     */
    public static function resolveContentPath($content, $channel)
    {
        return 'content/' . str_replace('[id]', $content['id'], ltrim($channel['content_path'], '/'));
    }


    /**
     * 处理站内地址
     * @param string $path
     * @return string
     */
    public static function resolveWebPath($path)
    {
        if (empty($path)) {
            return '';
        }
        if (preg_match('/^http(s)?:\/\//', $path)) {
            return $path;
        }

        return self::$path . ltrim($path, '/');
    }

    /**
     * 替换标签路径
     * 
     * @param array|mixed $tag
     * @return string
     */
    public static function resolveTagPath($tag)
    {
        return 'dynamic/tag-' . $tag['id'];
    }

    public static function getOutPath()
    {
        $outPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, App::getPublicPath() . ltrim(self::$path, '/'));
        if (!is_dir($outPath . 'channel/')) {
            mkdir($outPath . 'channel/', 0755, true);
        }
        if (!is_dir($outPath . 'content/')) {
            mkdir($outPath . 'content/', 0755, true);
        }

        return $outPath;
    }

    /**
     * 处理列表条目
     *
     * @param string $table
     * @param array $item
     * @return array|EmptyData
     */
    public static function item($table, $item)
    {
        if (empty($item)) {
            $empty = new EmptyData;
            return $empty;
        }

        $dbNameSpace = self::getDbNamespace();
        if ($table == 'cms_channel') {
            $item['channel_id'] = $item['id'];
            $item['url'] = static::resolveWebPath($item['link']) ?: ($item['channel_path'] == '#' ? '#' : self::$path . self::resolveChannelPath($item) . '.html');
        } else if ($table == 'cms_content') {

            if (empty($item['channel_id'])) {
                $channel = new EmptyData;
                $item['url'] = static::resolveWebPath($item['link']) ?: self::$path . self::resolveContentPath($item, ['content_path' => 'a[id]']) . '.html';
                $item['channel_url'] = '#';
            } else {
                $channelScope = Table::defaultScope($table);
                $channel = $dbNameSpace::name('cms_channel')
                    ->where('id', $item['channel_id'])
                    ->where($channelScope)
                    ->cache(static::$isAdmin ? false : 'cms_channel_' . $item['channel_id'], 0, 'cms_channel')
                    ->find();
                if ($channel) {
                    $item['url'] = static::resolveWebPath($item['link']) ?: self::$path . self::resolveContentPath($item, $channel) . '.html';
                    $item['channel_url'] = $channel['link'] ?: ($channel['channel_path'] == '#' ? '#' : self::$path . self::resolveChannelPath($channel) . '.html');
                } else {
                    $empty = new EmptyData;
                    return $empty;
                }
            }
            $item['channel'] = $channel;
            $item['content_id'] = $item['id'];
            $item = static::resolveContentDate($item);
        } else if ($table == 'cms_banner') {
            $item['url'] = static::resolveWebPath($item['link']);
        } else if ($table == 'cms_tag') {
            $item['url'] = self::$path . self::resolveTagPath($item);
        } else {
            $item['url'] = '#';
        }

        return $item;
    }

    /**
     * 处理数据详情
     *
     * @param string $table
     * @param array $item
     * @return array|EmptyData
     */
    public static function detail($table, $item)
    {
        if (empty($item)) {
            $empty = new EmptyData;
            return $empty;
        }

        $dbNameSpace = self::getDbNamespace();
        $item['__not_found__'] = false;
        if ($table == 'cms_channel') {
            $item['channel_id'] = $item['id'];
            $item['url'] = static::resolveWebPath($item['link']) ?: self::$path . self::resolveChannelPath($item) . '.html';
            $childrenIds = [];
            if ($item['type'] == 1 || $item['type'] == 2) { //不限|目录
                $channelScope = Table::defaultScope($table);
                $childrenIds = $dbNameSpace::name('cms_channel')
                    ->where('parent_id', $item['id'])
                    ->where($channelScope)
                    ->where('type', '<>', 2)
                    ->cache(static::$isAdmin ? false : 'cms_channel_children_ids_' . $item['channel_id'], 0, 'cms_channel')
                    ->column('id');
            }
            $item['children_ids'] = $childrenIds;
        } else if ($table == 'cms_content') {

            if (empty($item['channel_id'])) {
                $channel = new EmptyData;
                $item['url'] = static::resolveWebPath($item['link']) ?: self::$path . self::resolveContentPath($item, ['content_path' => 'a[id]']) . '.html';
                $item['channel_url'] = '#';
            } else {
                $channelScope = Table::defaultScope($table);
                $channel = $dbNameSpace::name('cms_channel')
                    ->where('id', $item['channel_id'])
                    ->where($channelScope)
                    ->cache(static::$isAdmin ? false : 'cms_channel_' . $item['channel_id'], 0, 'cms_channel')
                    ->find();
                if ($channel) {
                    $item['url'] = static::resolveWebPath($item['link']) ?: self::$path . self::resolveContentPath($item, $channel) . '.html';
                    $item['channel_url'] = $channel['link'] ?: ($channel['channel_path'] == '#' ? '#' : self::$path . self::resolveChannelPath($channel) . '.html');
                } else {
                    $channel = new EmptyData;
                }
            }
            $item['channel'] = $channel;
            $item['content_id'] = $item['id'];
            $item = static::resolveContentDate($item);

            $detail = null;
            if (!empty($item['reference_id'])) {
                $detail = $dbNameSpace::name('cms_content_detail')
                    ->where('main_id', $item['reference_id'])
                    ->cache(static::$isAdmin ? false : 'cms_content_detail_' . $item['reference_id'], 3600, $table)
                    ->find();
            } else {
                $detail = $dbNameSpace::name('cms_content_detail')
                    ->where('main_id', $item['id'])
                    ->cache(static::$isAdmin ? false : 'cms_content_detail_' . $item['id'], 3600, $table)
                    ->find();
            }
            $item['content'] = $detail ? $detail['content'] : '';
            $item['attachments'] = $detail ? $detail['attachments'] : '';
            $item['attachments_array'] = static::getAttachments($item);
        } else if ($table == 'cms_banner') {
            $item['url'] = static::resolveWebPath($item['link']);
        } else if ($table == 'cms_tag') {
            $item['url'] = self::$path . self::resolveTagPath($item);
        } else {
            $item['url'] = '#';
        }

        return $item;
    }

    /**
     * 处理内容时间字段
     * @param array $item
     * @return array
     */
    protected static function resolveContentDate($item)
    {
        $item['datetime'] = $item['publish_time'];
        $item['publish_date'] = date('Y-m-d', strtotime($item['publish_time'] ?? '2024-01-01'));
        $item['date'] = $item['publish_date'];
        $item['time'] = date('H:i:s', strtotime($item['publish_time'] ?? '2024-01-01'));
        $ymdArr = explode('-', $item['publish_date']);
        $item['yy'] = $ymdArr[0];
        $item['mm'] = $ymdArr[1];
        $item['dd'] = $ymdArr[2];

        return $item;
    }

    /**
     * 提前处理附件
     * @param array $item
     * @return array
     */
    protected static function getAttachments($item)
    {
        $files = [];
        if ($item['attachments']) {
            $attachments_array = array_filter(explode(',', $item['attachments']));
            $content_array = array_filter(explode("\n", strip_tags($item['content'])));
            foreach ($attachments_array as $k => $v) {
                $file = str_replace(['http://' . request()->host(), 'https://' . request()->host()], '', $v);
                $fileSize = is_file('./' . ltrim($file, '/')) ? filesize('./' . ltrim($file, '/')) : 0;
                if ($fileSize >= 1024 ** 2) {
                    $fileSize = round($fileSize / (1024 ** 2), 2) . 'MB';
                } else {
                    $fileSize = round($fileSize / 1024, 2) . 'KB';
                }
                $files[$file] = [
                    'file' => $file,
                    'desc' => trim($content_array[$k] ?? $file),
                    'size' => $fileSize,
                ];
            }
        }
        if (preg_match_all('/<a[^<>]*href=[\"\']([^<>\"\']+?\.(pdf|doc|docx|xls|xlsx|ppt|pptx|rar|zip|7z|tar|gz|bz2))[\"\'][^<>]*>(.+?)<\/a>/is', $item['content'], $mchs)) {
            foreach ($mchs[1] as $k => $mch) {
                $file = str_replace(['http://' . request()->host(), 'https://' . request()->host()], '', $mch);
                if (isset($files[$file])) {
                    continue;
                }
                $fileSize = is_file('./' . $mch) ? filesize('./' . $mch) : 0;
                if ($fileSize >= 1024 ** 2) {
                    $fileSize = round($fileSize / (1024 ** 2), 2) . 'MB';
                } else {
                    $fileSize = round($fileSize / 1024, 2) . 'KB';
                }
                $files[$file] = [
                    'file' => $file,
                    'desc' => trim(strip_tags($mchs[2][$k] ?? '')),
                    'size' => $fileSize,
                ];
            }
        }
        return $files;
    }

    public static function getParents($table, $id, $idKey = 'id', $pidKey = 'parent_id')
    {
        $list = [];
        $data = static::getData($table, $idKey, $id);
        if ($data) {
            $list = static::getUpperNodes($table, $idKey, $pidKey, $data, $list);
        }
        foreach ($list as &$li) {
            $li = static::item($table, $li);
        }
        return array_reverse($list);
    }

    public static function getUpperNodes($table, $idKey, $pidKey, $node, $list = [], $limit = 9)
    {
        foreach ($list as $li) {
            if ($li[$idKey] == $node[$idKey]) {
                trace("死循环:" . json_encode($node));
                return $list;
            }
        }
        if (count($list) >= $limit) {
            return $list;
        }
        $list[] = $node;
        if ($node[$pidKey] == 0 || $node[$pidKey] == $node[$pidKey]) {
            return $list;
        }
        $up = static::getData($table, $idKey, $node[$pidKey]);
        if ($up) {
            $list = static::getUpperNodes($table, $idKey, $pidKey, $up, $list, $limit);
        }
        return $list;
    }

    /**
     * Undocumented function
     *
     * @param string $table
     * @param int $id
     * @return array
     */
    public static function getData($table, $idKey, $id)
    {
        $dbNameSpace = self::getDbNamespace();

        return $dbNameSpace::name($table)->where($idKey, $id)->cache(static::$isAdmin ? false : $table . '_' . $id, 3600, $table)->find();
    }
}
