<?php

namespace app\admin\controller;

use app\common\model;
use think\Controller;
use tpext\myadmin\admin\model\AdminUser;

class Index extends Controller
{
    protected $currentAdmin;

    protected function initialize()
    {
        $this->currentAdmin = AdminUser::current();
    }

    public function dashbord()
    {
        if ($this->currentAdmin['role_id'] == 1 || $this->currentAdmin['group_id'] == 1) {
            return $this->page1();
        } else {
            return $this->page2();
        }
    }

    private function page1()
    {
        $order_count = model\ShopOrder::count();
        $goods_count = model\ShopGoods::count();
        $member_count = model\Member::count();
        $order_wait = model\ShopOrder::where(['pay_status' => 1, 'shipping_status' => 0])->count();

        $days  = [];
        $new_members = [];
        $recharges = [];

        for ($i = 6; $i >= 0; $i -= 1) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $days[] = $day;
            $where = [
                ['create_time', 'egt', $day . ' 00:00:00'],
                ['create_time', 'elt', $day . ' 23:59:59'],
            ];

            $new_members[] = model\Member::where($where)->count();
            $recharges[] = model\MemberRecharge::where($where)->where(['pay_status' => 1])->sum('account');
        }
        $days = json_encode($days);
        $new_members = json_encode($new_members);
        $recharges = json_encode($recharges);

        $vars = compact('order_count', 'goods_count', 'member_count', 'order_wait', 'days', 'new_members', 'recharges');

        return $this->fetch('', $vars);
    }

    private function page2()
    {
        return $this->fetch('dashbord2');
    }
}
