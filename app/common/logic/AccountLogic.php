<?php

namespace app\common\logic;

use app\common\model;
use think\facade\Db;
use think\facade\Log;

class AccountLogic
{
    /**
     * Undocumented function
     *
     * @param int $member_id
     * @param array $data
     * @return array
     */
    public function editMemberAccount($member_id, $data)
    {
        $this->errors = [];
        $data = array_merge([
            'member_id' => $member_id,
            'points' => 0,
            'money' => 0,
            'commission' => 0,
            'remark' => '',
            'admin_id' => session('admin_id') ?: 0,
            'order_id' => 0,
            'tag' => '',
        ], $data);

        if ($data['points'] == 0 && $data['money'] == 0 && $data['commission'] == 0) {
            return ['code' => 0, 'msg' => model\MemberAccount::getNames() . '不能全部为 0'];
        }

        Db::startTrans();

        $sql = '';
        try
        {
            $prefix = config('database.connections.mysql.prefix', '');

            $sql = "UPDATE {$prefix}member SET points = points + {$data['points']}," .
                " money = money + {$data['money']}, commission = commission + {$data['commission']}"
                . " WHERE id = $member_id";

            $res0 = Db::execute($sql);

            $memberAccountModel = new model\MemberAccount();
            $res1 = $memberAccountModel->save($data);

            if ($res0 && $res1) {
                Db::commit();

                return ['code' => 1, 'msg' => 'ok'];
            } else {
                Log::alert('执行sql失败：' . $sql);
                Db::rollback();

                return ['code' => 0, 'msg' => '未知错误-sql有误或无更改'];
            }
        } catch (\Exception $e) {
            Db::rollback();

            $msg = $e->getMessage();

            $types = model\MemberAccount::$types;

            if (preg_match('/.+?Out\s+of\s+range\s+value\s+for\s+column\s+[\'\"`](\w+)[\'\"`]/im', $msg, $matchs)) {
                $msg = (isset($types[$matchs[1]]) ? $types[$matchs[1]] : $matchs[1]) . '不足';
            }

            Log::alert('执行sql出错：' . $sql);
            Log::error($e->getMessage());

            return ['code' => 0, 'msg' => $msg];
        }
    }

    /**
     * Undocumented function
     *
     * @param int $member_id
     * @param int|float $points
     * @return array
     */
    public function editMemberPoints($member_id, $points, $remark = '', $tag = '')
    {
        $data = [
            'points' => $points,
            'remark' => $remark,
            'tag' => $tag,
        ];

        return $this->editMemberAccount($member_id, $data);
    }

    /**
     * Undocumented function
     *
     * @param int $member_id
     * @param int|float $money
     * @return array
     */
    public function editMemberMoney($member_id, $money, $remark = '', $tag = '')
    {
        $data = [
            'money' => $money,
            'remark' => $remark,
            'tag' => $tag,
        ];

        return $this->editMemberAccount($member_id, $data);
    }

    /**
     * Undocumented function
     *
     * @param int $member_id
     * @param int|float $commission
     * @return array
     */
    public function editMemberCommission($member_id, $commission, $remark = '', $tag = '')
    {
        $data = [
            'commission' => $commission,
            'remark' => $remark,
            'tag' => $tag,
        ];

        return $this->editMemberAccount($member_id, $data);
    }

    public function hasEnouth($member_id, $type, $num)
    {
        $memberModel = new model\Member();

        $member = $memberModel->find($member_id);
        if (!$member) {
            return ['code' => 0, 'msg' => '会员不存在' . $member_id, 'current' => 0];
        }
        if ($num <= 0) {
            return ['code' => 0, 'msg' => '输入值不能小于等于0', 'current' => 0];
        }

        if (!isset($member[$type])) {
            return ['code' => 0, 'msg' => '参数错误-' . $type, 'current' => 0];
        }

        if ($member[$type] < $num) {
            return ['code' => 0, 'msg' => model\MemberAccount::$types[$type] . '不足,最多可用' . $member[$type], 'current' => $member[$type]];
        }

        return ['code' => 1, 'msg' => 'ok', 'current' => $member[$type]];
    }
}
