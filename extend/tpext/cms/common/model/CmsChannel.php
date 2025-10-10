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
use think\model\concern\SoftDelete;
use tpext\builder\traits\TreeModel;

class CmsChannel extends Model
{
    use TreeModel;
    use SoftDelete;

    protected $name = 'cms_channel';

    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::where(['parent_id' => $data['parent_id']])->max('sort') + 5;
        }
    }

    public static function onAfterInsert($data)
    {
        if (!empty($data['parent_id'])) {
            Cache::delete('cms_channel_children_ids_' . $data['parent_id']);
        }

        Cache::deleteTag('cms_channel');

        ExtLoader::trigger('cms_channel_on_after_insert', $data);
    }

    public static function onAfterUpdate($data)
    {
        if (!isset($data['id'])) {
            return;
        }
        Cache::delete('cms_channel_' . $data['id']);

        if (!empty($data['parent_id'])) {
            Cache::delete('cms_channel_children_ids_' . $data['parent_id']);
        }

        Cache::deleteTag('cms_channel');

        ExtLoader::trigger('cms_channel_on_after_update', $data);

        //循环触发子栏目更新
        $children = static::where('parent_id', $data['id'])->select();
        foreach ($children as $child) {
            $child['update_time'] = date('Y-m-d H:i:s');
            $child->save();
        }
    }

    public static function onBeforeWrite($data)
    {
        if (isset($data['parent_id'])) {
            if ($data['parent_id'] == 0) {
                $data['full_name'] = $data['name'];
                $data['path'] = ',0,';
                $data['deep'] = 1;
            } else {
                $upNodes = (new static)->getUpperNodes($data);
                $names = [];
                $ids = [];
                foreach ($upNodes as $node) {
                    $names[] = $node['name'];
                    $ids[] = $node['id'];
                }
                $data['full_name'] = implode(' / ', array_reverse($names));
                $data['path'] = implode(',', array_reverse($ids));
                $data['deep'] = count($ids);
            }
        }
    }

    public static function onAfterDelete($data)
    {
        static::where(['parent_id' => $data['id']])->update(['parent_id' => $data['parent_id']]);
        CmsContent::where(['channel_id' => $data['id']])->update(['channel_id' => $data['parent_id']]);

        Cache::delete('cms_channel_' . $data['id']);
        Cache::delete('cms_channel_children_ids_' . $data['parent_id']);

        Cache::deleteTag('cms_channel');

        ExtLoader::trigger('cms_channel_on_after_delete', $data);
    }

    protected function treeInit()
    {
        $this->treeTextField = 'name';
        $this->treeSortField = 'sort';
    }

    public function getContentCountAttr($value, $data)
    {
        return CmsContent::where('channel_id', $data['id'])
            ->cache('content_count_' . $data['id'], 60 * 60 * 24, 'cms_content')
            ->count();
    }

    public function bindhtmls()
    {
        return $this->hasMany(CmsContentPage::class, 'to_id', 'id')
            ->where('html_type', 'in', ['channel', 'content'])
            ->where('template_id', 1);
    }

    /**
     * Undocumented function
     *
     * @param array $membnodeer 用户
     * @param array $list 已获取的上级列表，第一次是传空数组
     * @param integer $limit 层级限制
     * @return array
     */
    public function getUpperNodes($node, $list = [], $limit = 999)
    {
        if (!isset($node['id'])) {
            $node['id'] = 0;
        }
        foreach ($list as $li) {
            if ($li['id'] == $node['id']) {
                trace("死循环:" . json_encode($node));
                return $list;
            }
        }
        if (count($list) >= $limit) {
            return $list;
        }

        $list[] = $node;

        if ($node['parent_id'] == 0 || $node['parent_id'] == $node['id']) {
            return $list;
        }

        $up = $this->getUpper($node['parent_id']);

        if ($up) {
            $list = $this->getUpperNodes($up, $list, $limit);
        }

        return $list;
    }

    protected function getUpper($parent_id)
    {
        if (empty($this->allTreeData)) {
            $this->allTreeData = static::select();
        }
        foreach ($this->allTreeData as $data) {
            if ($data['id'] == $parent_id) {
                return $data;
            }
        }
        return null;
    }

    public function getChannelPathAttr($value, $data)
    {
        if (empty($value)) {
            $value = '[id]';
        }
        return $value;
    }

    public function getContentPathAttr($value, $data)
    {
        if (empty($value)) {
            $value = '[id]';
        }
        return $value;
    }

    public function setExtendIdsAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return is_array($value) ? implode(',', $value) : trim($value, ',');
    }

    public function setModelIdsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : trim($value, ',');
    }
}
