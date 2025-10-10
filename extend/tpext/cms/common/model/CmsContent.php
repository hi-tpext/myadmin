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

class CmsContent extends Model
{
    use SoftDelete;
    protected $name = 'cms_content';
    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onBeforeWrite($data)
    {
        $arrayData = $data->toArray();

        if (empty($data['description']) && empty($data['reference_id']) && isset($arrayData['content'])) {
            $content = $data->getData('content');
            $content = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $content);
            $content = strip_tags($content);
            $content = preg_replace('/[\r|\n|\t|\s]/is', '', $content);
            $content = str_replace(['\u00A0', '\u0020', '\u2800', '\u3000', '　', '&nbsp;', '&gt;', '&lt;', '&eq;', '&egt;', '&elt;'], '', $content);
            $data['description'] = static::getDesc($content);
        }

        if (empty($data['publish_time'])) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }

        ExtLoader::trigger('cms_content_on_before_write', $data);
    }

    public static function onAfterInsert($data)
    {
        if (!isset($data['id'])) {
            return;
        }

        $arrayData = $data->toArray();

        $detailData = [
            'content' => '',
            'attachments' => ''
        ];

        if (isset($arrayData['content'])) {
            $detailData['content'] = $data->getData('content');
        }
        if (isset($arrayData['attachments'])) {
            $detailData['attachments'] = $data->getData('attachments');
        }

        if (!empty($detailData)) {
            $detailData['main_id'] = $data['id'];
        }

        $detail = new CmsContentDetail;

        if (isset($data['reference_id']) && $data['reference_id'] > 0) {
            $detail->save([
                'main_id' => $data['id'],
                'content' => '@' . $data['reference_id'],
                'attachments' => '@' . $data['reference_id']
            ]);
        } else {
            $arrayData = $data->toArray();
            $detailData = [
                'content' => '',
                'attachments' => ''
            ];
            if (isset($arrayData['content'])) {
                $detailData['content'] = $data->getData('content');
            }
            if (isset($arrayData['attachments'])) {
                $detailData['attachments'] = $data->getData('attachments');
            }

            if ($detailData['content'] || $detailData['attachments']) {
                $detailData['main_id'] = $data['id'];
                $detail->save($detailData);
            }
        }

        if (!empty($data['channel_id'])) {
            Cache::delete('content_count_' . $data['channel_id']);
        }

        ExtLoader::trigger('cms_content_on_after_insert', $data);
    }

    public static function onAfterUpdate($data)
    {
        if (!isset($data['id'])) {
            return;
        }

        Cache::delete('cms_content_' . $data['id']);
        Cache::delete('cms_content_click_' . $data['id']);
        Cache::delete('cms_content_detail_' . $data['id']);

        $detail = CmsContentDetail::where('main_id', $data['id'])->find();
        if (!$detail) {
            $detail = new CmsContentDetail;
        } else {
            Cache::delete('cms_content_detail_' . $detail['id']);
        }

        if (isset($data['reference_id']) && $data['reference_id'] > 0) {
            $detail->save([
                'main_id' => $data['id'],
                'content' => '@' . $data['reference_id'],
                'attachments' => '@' . $data['reference_id']
            ]);
        } else {
            $arrayData = $data->toArray();
            $detailData = [
                'content' => '',
                'attachments' => ''
            ];
            if (isset($arrayData['content'])) {
                $detailData['content'] = $data->getData('content');
            }
            if (isset($arrayData['attachments'])) {
                $detailData['attachments'] = $data->getData('attachments');
            }

            if ($detailData['content'] || $detailData['attachments']) {
                $detailData['main_id'] = $data['id'];
                $detail->save($detailData);
            }

            self::where('reference_id', $data['id'])
                ->update([
                    'title' => $data['title'] ?? '',
                    'keywords' => $data['keywords'] ?? '',
                    'link' => $data['link'] ?? '',
                    'description' => $data['description'] ?? '',
                    'author' => $data['author'] ?? '',
                    'source' => $data['source'] ?? '',
                    'logo' => $data['logo'] ?? ''
                ]);
        }

        ExtLoader::trigger('cms_content_on_after_update', $data);
    }

    public static function onAfterDelete($data)
    {
        CmsContentDetail::where('main_id', $data['id'])->delete();

        Cache::delete('cms_content_' . $data['id']);
        Cache::delete('cms_content_detail_' . $data['id']);
        if (!empty($data['channel_id'])) {
            Cache::delete('content_count_' . $data['channel_id']);
        }

        ExtLoader::trigger('cms_content_on_after_delete', $data);
    }

    protected static function getDesc($content)
    {
        $arr = explode('。', $content);
        $text = '';
        $n = 0;
        foreach ($arr as $a) {
            if (mb_strlen($text . $a . '。') > 255) {
                if ($n == 0) {
                    $a = mb_substr($a, 0, 254);
                    $arr2 = explode('，', $a);
                    if (count($arr2) > 1) {
                        array_pop($arr2);
                        $text = implode('，', $arr2) . '。';
                    } else {
                        $text = $a . '。';
                    }
                }
                break;
            }
            $text .= $a . '。';
            $n += 1;
            if (mb_strlen($text) > 90) {
                break;
            }
            if ($n > 2) {
                break;
            }
        }

        return $text;
    }

    public function channel()
    {
        return $this->belongsTo(CmsChannel::class, 'channel_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne(CmsContentDetail::class, 'main_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(CmsContentModel::class, 'model_id', 'id');
    }

    public function getContentAttr($value, $data)
    {
        if (!empty($data['reference_id'])) {
            $detail = CmsContentDetail::where('main_id', $data['reference_id'])->find();
            return $detail ? $detail['content'] : '';
        }

        return isset($this->detail) ? $this->detail['content'] : '';
    }

    public function getAttachmentsAttr($value, $data)
    {
        if (!empty($data['reference_id'])) {
            $detail = CmsContentDetail::where('main_id', $data['reference_id'])->find();
            return $detail ? $detail['attachments'] : '';
        }

        return isset($this->detail) ? $this->detail['attachments'] : '';
    }

    public function getAttrAttr($value, $data)
    {
        $attr = [];
        if ($data['is_recommend']) {
            $attr[] = 'is_recommend';
        }
        if ($data['is_hot']) {
            $attr[] = 'is_hot';
        }
        if ($data['is_top']) {
            $attr[] = 'is_top';
        }

        return $attr;
    }

    public function setTagsAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return is_array($value) ? implode(',', $value) : trim($value, ',');
    }

    public function setMentionIdsAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return is_array($value) ? implode(',', $value) : trim($value, ',');
    }

    public function getTagNamesAttr($value, $data)
    {
        $ids = trim($data['tags'], ',');
        if (empty($ids)) {
            return '';
        }
        $list = CmsTag::where('id', 'in', $ids)->column('name');
        return implode(',', $list);
    }

    public function getClickAttr($value, $data)
    {
        if (empty($data['id'])) {
            return 0;
        }
        return Cache::get('cms_content_click_' . $data['id']) ?: $data['click'];
    }

    public function getPublishDateAttr($value, $data)
    {
        return date('Y-m-d', strtotime($data['publish_time']));
    }

    public function getDateAttr($value, $data)
    {
        return date('Y-m-d', strtotime($data['publish_time']));
    }

    public function getTimeAttr($value, $data)
    {
        return date('H:i:s', strtotime($data['publish_time']));
    }

    public function getYyAttr($value, $data)
    {
        return date('Y', strtotime($data['publish_time']));
    }

    public function getMmAttr($value, $data)
    {
        return date('m', strtotime($data['publish_time']));
    }

    public function getDdAttr($value, $data)
    {
        return date('d', strtotime($data['publish_time']));
    }
}
