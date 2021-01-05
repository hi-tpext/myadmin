<?php

namespace app\common\model;

use think\Model;
use app\common\Module;
use think\model\concern\SoftDelete;
use tpext\myadmin\admin\model\AdminUser;

class ShopGoods extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'datetime';

    public static function onBeforeInsert($data)
    {
        if (empty($data['sort'])) {
            $data['sort'] = static::max('sort') + 5;
        }
    }

    public static function onBeforeWrite($data)
    {
        if (empty($data['images'])) {
            $data['images'] = isset($data['logo']) ? $data['logo'] : '';
        }
    }

    public function cate()
    {
        return $this->belongsTo('shop_category', 'category_id', 'id');
    }

    public function getAdminGroupAttr($value, $data)
    {
        $model = new \tpext\myadmin\admin\model\AdminUser;
        $model = $model->getAdminGroupModel();
        $category = $model->find($data['admin_group_id']);
        return $category ? $category['name'] : '--';
    }

    public function getCategoryAttr($value, $data)
    {
        $category = ShopCategory::find($data['category_id']);
        return $category ? $category['name'] : '--';
    }

    public function setTags($value)
    {
        if (empty($value)) {
            return '';
        }

        return is_array($value) ? ',' . implode(',', $value) . ',' : ',' . trim($value, ',') . ',';
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

    public function getSpuAttr($value, $data)
    {
        if (empty($value) && isset($data['id'])) {
            return 'sp' . str_pad($data['id'], 7, "0", STR_PAD_LEFT);
        }

        return $value;
    }

    public function getManyPriceAttr($value, $data)
    {
        return ShopGoodsSpecPrice::where(['goods_id' => $data['id']])->count() > 0 ? 1 : 0;
    }

    public function category()
    {
        return $this->belongsTo(Shopcategory::class, 'category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Shopbrand::class, 'brand_id', 'id');
    }
}
