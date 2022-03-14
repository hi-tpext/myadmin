<?php

namespace app\common\logic;

use app\common\model;
use think\Validate;

class GoodsLogic
{
    /**
     * Undocumented function
     *
     * @param int $goodsId
     * @param string $type
     * @param int $changes
     * @return array
     */
    public function savelsit($goodsId, $type, &$changes = 0)
    {
        $postModels = [];
        if ($type == 'spec_list') {
            $postModels = input('post.spec_list/a', []);
        } else {
            $postModels = input('post.attr_list/a', []);
        }

        $errors = [];
        $names = [];

        $valedate = new Validate([
            'name|名称' => 'require',
            'value|值' => 'require',
        ]);

        foreach ($postModels as $key => &$pdata) {

            $dataModel = $type == 'spec_list' ? new model\ShopGoodsSpec() : new model\ShopGoodsAttr();

            $result = $valedate->check($pdata);

            if (true !== $result) {
                $errors[] = '规格/属性[' . $pdata['name'] . ']' . $result;
                continue;
            }

            $values = array_filter(array_filter(explode(',', $pdata['value']), 'strlen'), 'trim');
            $pdata['value'] = implode(',', $values);
            $pdata['goods_id'] = $goodsId;

            $is_del = isset($pdata['__del__']) && $pdata['__del__'] == 1;
            $is_add = strpos($key, '__new__') !== false;

            if (!$is_del && in_array($pdata['name'], $names)) {
                $errors[] = '规格/属性[' . $pdata['name'] . ']重复，请修改';
                if ($is_add) {
                    continue;
                }
            }

            if ($is_add) {
                $res = $dataModel->exists(false)->save($pdata);
                if ($res) {
                    $names[] = $pdata['name'];
                    if ($type == 'spec_list') {
                        $this->saveSpecValues($dataModel['id'], $goodsId, $values);
                    }
                    $changes += 1;
                } else {
                    $errors[] = '规格/属性[' . $pdata['name'] . ']保存出错';
                }
            } else {
                if ($is_del) {
                    $res = $dataModel::destroy($key);
                    if ($res) {
                        $changes += 1;
                        if ($type == 'spec_list') {
                            model\ShopGoodsSpecValue::where(['spec_id' => $key])->delete(); //删除规格对应值
                        }
                    }
                } else {
                    $exist = $dataModel->find($key);
                    if (!$exist) {
                        continue;
                    }
                    if ($exist['goods_id'] == $pdata['goods_id'] && $exist['value'] == $pdata['value'] && $exist['name'] == $pdata['name']) {
                        continue;
                    }
                    $res = $exist->save($pdata);
                    if ($res) {
                        $names[] = $pdata['name'];
                        if ($type == 'spec_list') {
                            $this->saveSpecValues($key, $goodsId, $values);
                        }
                        $changes += 1;
                    } else {
                        $errors[] = '规格/属性[' . $pdata['name'] . ']保存出错';
                    }
                }
            }
        }

        if (model\ShopGoodsSpec::where(['goods_id' => $goodsId])->count() == 0) {
            model\ShopGoodsSpecValue::where(['goods_id' => $goodsId])->delete();
            model\ShopGoodsSpecPrice::where(['goods_id' => $goodsId])->delete();
        }

        return $errors;
    }

    /**
     * Undocumented function
     *
     * @param int $goodsId
     * @return array
     */
    public function savePrices($goodsId)
    {
        $priceList = input('post.price_list/a', []);

        $specModel = new model\ShopGoodsSpec();
        $specValueModel = new model\ShopGoodsSpecValue();

        $sids = $specModel->where(['goods_id' => $goodsId])->order('sort')->column('id');

        if (empty($sids)) {
            return [];
        }

        $valedate = new Validate([
            'sale_price|销售价格' => 'require|float',
            'stock|库存' => 'require|integer',
        ]);

        $mvals = [];

        $specIds = '_' . implode('_', $sids) . '_';
        $specKeyIds = [];

        $errors = [];

        foreach ($priceList as $key => &$pdata) {

            $goodsPriceModel = new model\ShopGoodsSpecPrice;

            $result = $valedate->check($pdata);

            if (true !== $result) {
                $errors[] = '规格/属性有误：' . $result;
                continue;
            }

            $is_del = isset($pdata['__del__']) && $pdata['__del__'] == 1;
            $is_add = strpos($key, '__new__') !== false;

            $mvals = [];
            $modelNames = [];

            $data = [];

            foreach ($sids as $sid) {
                $selected = isset($pdata['spec_id' . $sid]) ? $pdata['spec_id' . $sid] : '0';
                $mvals[] = $selected;
                $data[$sid] = $selected;

                $valueInfo = $specValueModel->find($selected);
                $modelNames[] = $valueInfo ? str_replace('*', '＊', $valueInfo['value']) : '-'; //规格名称里面的*号替换一下，避免混淆
            }

            if (empty($mvals)) {
                if (!$is_add) {
                    $goodsPriceModel->destroy($key);
                }
                continue;
            }

            sort($mvals); //排序，前台传过来的也要排序，一样的话就能匹配。1,2.3 | 2,3,1 | 3,1,2 其实是一样的

            $pdata['spec_key'] = '_' . implode('_', $mvals) . '_';
            $pdata['data'] = json_encode($data);
            $pdata['spec_key_name'] = implode('*', $modelNames);
            $pdata['goods_id'] = $goodsId;
            $pdata['spec_ids'] = $specIds;

            if (!$is_del && in_array($pdata['spec_key'], $specKeyIds)) {
                $errors[] = '价格/库存组合[' . $pdata['spec_key_name'] . ']重复';
                if ($is_add) {
                    continue;
                }
                if (empty($pdata['sku'])) {
                    $pdata['sku'] = '-';
                }
            }

            if ($is_add) {
                $res = $goodsPriceModel->save($pdata);
                if ($res) {
                    $specKeyIds[] = $pdata['spec_key'];
                } else {
                    $errors[] = '价格/库存[' . $pdata['spec_key_name'] . ']保存出错';
                }
            } else {
                if ($is_del) {
                    $goodsPriceModel->destroy($key);
                } else {
                    $exist = $goodsPriceModel->find($key);
                    $res = $exist && $exist->save($pdata);
                    if ($res) {
                        $specKeyIds[] = $pdata['spec_key'];
                    } else {
                        $errors[] = '价格/库存[' . $pdata['spec_key_name'] . ']保存出错';
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Undocumented function
     *
     * @param int $model_id
     * @param array $values
     * @return void
     */
    public function saveSpecValues($spec_id, $goods_id, $values)
    {
        if (empty($spec_id) || empty($goods_id)) {
            return;
        }

        $specValueModel = new model\ShopGoodsSpecValue();

        $allIds = $specValueModel->where(['spec_id' => $spec_id])->column('id');

        $inUseIds = [];
        foreach ($values as $v) {
            $mv = $specValueModel->where(['spec_id' => $spec_id, 'value' => $v])->find();
            if (!$mv) {
                $specValueModel->create([
                    'spec_id' => $spec_id,
                    'value' => $v,
                    'goods_id' => $goods_id,
                ]);
            } else {
                $inUseIds[] = $mv['id'];
            }
        }

        $notInUseIds = array_diff($allIds, $inUseIds);

        if (!empty($notInUseIds)) {
            $specValueModel->destroy(array_values($notInUseIds));
        }
    }

    public function getSpecList($q = '')
    {
        $where = [];

        if ($q) {
            $where[] = ['name|spu', 'like', '%' . $q . '%'];
        }

        $goodsModel = new model\ShopGoods();
        $specPriceModel = new model\ShopGoodsSpecPrice();

        $specList = [];
        $goodsList = $goodsModel->where($where)->select();
        $priceList = [];

        foreach ($goodsList as $goods) {
            $priceList = $specPriceModel->where(['goods_id' => $goods['id']])->select();

            if (count($priceList)) { //有多种规格
                foreach ($priceList as $price) {
                    $specList[] = [
                        'id' => $price['spec_key'],
                        'text' => $goods['name'] . '#' . $price['sku'] . '[' . $price['spec_key_name'] . ']￥' . $price['sale_price'] . '库存:' . $price['stock'],
                    ];
                }
            } else { //无多种规格
                $specList[] = [
                    'id' => 'g_' . $goods['id'],
                    'text' => $goods['name'] . '#' . $goods['spu'] . '[*无规格*]￥' . $goods['sale_price'] . '库存:' . $goods['stock'],
                ];
            }
        }

        return $specList;
    }
}
