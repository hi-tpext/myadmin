<?php

namespace app\admin\controller;

use app\admin\logic\OrderLogic as AdminOrderLogic;
use app\common\logic\OrderLogic;
use app\common\model\ShopOrderAction as OrderActionModel;
use app\common\model\ShopOrder as OrderModel;
use think\Controller;
use tpext\builder\traits\actions;

/**
 * Undocumented class
 * @title 订单日志
 */
class Shoporderaction extends Controller
{
    use actions\HasIndex;
    use actions\HasBase;

    /**
     * Undocumented variable
     *
     * @var OrderActionModel
     */
    protected $dataModel;

    /**
     * Undocumented variable
     *
     * @var OrderModel
     */
    protected $orderModel;

    protected function initialize()
    {
        $this->dataModel = new OrderActionModel;
        $this->orderModel = new OrderModel;

        $this->pageTitle = '订单日志';
        $this->sortOrder = 'id desc';
        $this->pagesize = 14;
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];
        if (!empty($searchData['order_sn'])) {
            $where[] = ['order_sn', 'like', "%{$searchData['order_sn']}%"];
        }
        return $where;
    }

    /**
     * 构建搜索
     *
     * @return void
     */
    protected function buildSearch()
    {
        $search = $this->search;

        $search->text('order_sn', '订单sn', 4);
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;

        $table->show('order_id', '订单id');
        $table->show('order_sn', '订单sn')->getWrapper()->addStyle('widht:200px;');
        $table->show('action_note', '操作备注');
        $table->show('status_desc', '描述');
        $table->match('order_status', '订单状态')->options(OrderModel::$order_status_types);
        $table->match('pay_status', '支付状态')->options(OrderModel::$pay_status_types);
        $table->match('shipping_status', '物流状态')->options(OrderModel::$shipping_status_types);
        $table->show('create_time', '时间');

        $table->getToolbar()->btnRefresh();

        $table->useActionbar(false);
    }

    /**
     * Undocumented function
     * @title 订单操作
     *
     * @param int $id
     * @return void
     */
    public function orderAction($id)
    {
        $order = $this->orderModel->get($id);
        if (!$order) {
            return json([
                'code' => 0,
                'msg' => '订单不存在！',
            ]);
        }
        $adminOrderLogic = new AdminOrderLogic;
        $orderLogic = new OrderLogic(0);

        $action = input('post.action');
        $text = input('post.text');

        if ($action && $id) {
            $res = $orderLogic->logOrder($id, $text ? $text : $action, input('post.remark', ''));
            $a = $adminOrderLogic->orderProcessHandle($id, $action);
            if ($res && $a) {
                return json([
                    'code' => 1,
                    'msg' => '操作成功',
                ]);
            } else {
                return json([
                    'code' => 0,
                    'msg' => '操作失败',
                ]);
            }
        } else {
            return json([
                'code' => 0,
                'msg' => '参数错误',
            ]);
        }
    }

    /**
     * Undocumented function
     * @title 取消付款
     *
     * @param int $id
     * @return void
     */
    public function payCancel($id)
    {
        if (request()->isAjax()) {
            $order = $this->orderModel->get($id);
            if (!$order) {
                return json([
                    'code' => 0,
                    'msg' => '订单不存在！',
                ]);
            }
            $data = request()->post();

            $result = $this->validate($data, [
                'refund_type|退款方式' => 'require',
                'amount|收货地址' => 'require|float|egt:0',
            ]);

            if (true !== $result) {
                $this->error($result);
            }

            if ($data['refund_type'] == 1) {
                return json([
                    'code' => 0,
                    'msg' => '开发中...',
                ]);
            }
            $adminOrderLogic = new AdminOrderLogic;

            $res = $adminOrderLogic->orderPayCancel($id, $data['refund_type'], $data['amount']);
            if ($res['code'] != 1) {
                return json($res);
            }
            return $this->builder()->layer()->closeRefresh(1, $res['msg']);
        } else {
            $builder = $this->builder('取消支付');
            $order = $this->orderModel->get($id);

            if (!$order) {
                return $builder->layer()->closeRefresh(0, '订单不存在！');
            }

            $form = $this->builder('取消支付')->form();
            $form->radio('refund_type', '退款方式')->options([0 => '退款到用户余额', 1 => '原路退回第三方支付账户[开发中...]', 2 => '已通过其他方式退款']);
            $form->text('amount', '退款金额')->size(2, 2)->default($order['order_amount'])->afterSymbol('元');
            $form->textarea('remark', '退款说明');
            return $builder->render();
        }
    }

    /**
     * Undocumented function
     * @title 订单取消
     *
     * @return void
     */
    public function orderCancel()
    {
        $id = input('ids');
        $order = $this->orderModel->get($id);
        if (!$order) {
            return json([
                'code' => 0,
                'msg' => '订单不存在！',
            ]);
        }
        $adminOrderLogic = new AdminOrderLogic;
        $orderLogic = new OrderLogic(0);

        $action = 'ordre_cancel';
        $text = '取消订单';

        if ($action && $id) {
            $res = $orderLogic->logOrder($id, $text ? $text : $action, '');
            $a = $adminOrderLogic->orderProcessHandle($id, $action);
            if ($res && $a) {
                return json([
                    'code' => 1,
                    'msg' => '操作成功',
                ]);
            } else {
                return json([
                    'code' => 0,
                    'msg' => '操作失败',
                ]);
            }
        } else {
            return json([
                'code' => 0,
                'msg' => '参数错误',
            ]);
        }
    }

    /**
     * Undocumented function
     * @title 导入发货单
     *
     * @return mixed
     */
   /* public function importShipping()
    {
        $fileurl = input('fileurl');
        if (is_file(app()->getRootPath() . 'public' . $fileurl)) {
            // 导入逻辑...
            return $this->builder()->layer()->closeRefresh(1, '导入成功：' . $fileurl);
        }

        $builder = $this->builder('出错了');
        $builder->content()->display('<p>' . '未能读取文件:' . $fileurl . '</p>');
        return $builder->render();
    }*/
}
