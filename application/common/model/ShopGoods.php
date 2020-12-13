<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class ShopGoods extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'dateTime';

    protected static function init()
    {
        self::beforeInsert(function ($data) {
            if (empty($data['sort'])) {
                $data['sort'] = static::max('sort') + 5;
            }
        });

        self::beforeWrite(function ($data) {
            if (empty($data['images'])) {
                $data['images'] = isset($data['logo']) ? $data['logo'] : '';
            }
        });
    }

    public function cate()
    {
        return $this->hasOne('shop_category', 'category_id');
    }

    public function getAdminGroupAttr($value, $data)
    {
        $model = new \tpext\myadmin\admin\model\AdminUser;
        $model = $model->getAdminGroupModel();
        $category = $model->get($data['admin_group_id']);
        return $category ? $category['name'] : '--';
    }

    public function getCategoryAttr($value, $data)
    {
        $category = ShopCategory::get($data['category_id']);
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
        return $this->hasOne(Shopcategory::class, 'id', 'category_id');
    }

    public function brand()
    {
        return $this->hasOne(Shopbrand::class, 'id', 'brand_id');
    }
}
