<?php

namespace app\common\logic;

use app\common\model;
use think\facade\Log;

class MemberLogic
{
    public function changeLeader($member_id, $leader_id, $desc = '修改上级')
    {
        if (!$member_id) {
            return ['code' => 0, 'msg' => '参数有误'];
        }

        if ($member_id == $leader_id) {
            return ['code' => 0, 'msg' => '上级不能是自己'];
        }

        $member = model\Member::find($member_id);

        if (!$member) {
            return ['code' => 0, 'msg' => '用户不存在'];
        }

        if ($leader_id == -1) {
            $res = model\Member::where(['id' => $member_id])->update([
                'first_leader' => 0,
                'second_leader' => 0,
                'third_leader' => 0,
                ['relation' => "," . $member['id'] . ","],
            ]);

            if (!$res) {
                return ['code' => 0, 'msg' => '上级代理未能修改'];
            }

            model\MemberLog::create([
                'member_id' => $member_id,
                'desc' => $desc,
                'change' => $member['first_leader'] . '=>' . 0,
                'create_time' => date('Y-m-d  H:i:s'),
            ]);

            model\Member::where('relation', 'like', '%,' . $member_id . ',%')->update(['relation' => '']); //修改上级，整条关系线重置，等待重新计算

            return ['code' => 1, 'msg' => '修改上级代理成功'];
        }

        if ($member['first_leader'] == $leader_id) {
            return ['code' => 0, 'msg' => '已经是他上级，不能重复操作'];
        }

        $leader = model\Member::find($leader_id);

        if (!$leader) {
            return ['code' => 0, 'msg' => '上级用户不存在'];
        }

        if ($leader['first_leader'] == $member_id) {
            return ['code' => 0, 'msg' => '不能互为上下级'];
        }

        $res = model\Member::where(['id' => $member_id])->update([
            'first_leader' => $leader_id,
            'second_leader' => $leader['first_leader'],
            'third_leader' => $leader['second_leader'],
        ]);

        if (!$res) {
            return ['code' => 0, 'msg' => '上级代理未能修改'];
        }

        model\MemberLog::create([
            'member_id' => $member_id,
            'desc' => $desc,
            'change' => $member['first_leader'] . '=>' . $leader_id,
            'create_time' => date('Y-m-d  H:i:s'),
        ]);

        $member = model\Member::find($member_id);

        model\Member::where('relation', 'like', '%,' . $member_id . ',%')->update(['relation' => '']); //修改上级，整条关系线重置，等待重新计算

        $this->getLink($member);

        return ['code' => 1, 'msg' => '修改上级代理成功'];
    }

    public function changeLevel($member_id, $change_level, $desc = '修改等级')
    {
        if (empty($member_id) || $change_level < 0) {
            return ['code' => 0, 'msg' => '参数有误'];
        }

        $member = model\Member::find($member_id);

        if (!$member) {
            return ['code' => 0, 'msg' => '用户不存在'];
        }

        if ($member['level'] == $change_level) {
            return ['code' => 0, 'msg' => '新旧等级一样'];
        }

        $level = model\MemberLevel::where('level', $change_level)->find();

        if ($change_level > 0 && !$level) {
            return ['code' => 0, 'msg' => '等级不存在'];
        }

        $res = model\Member::where(['id' => $member_id])->update(['level' => $change_level]);
        if ($res) {
            model\MemberLog::create([
                'member_id' => $member_id,
                'desc' => $desc,
                'change' => $level ? $level['name'] : '--',
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            return ['code' => 1, 'msg' => '修改等级成功'];
        }

        return ['code' => 0, 'msg' => '等级未更新'];
    }

    public function changeAgentLevel($member_id, $change_agent_level, $desc = '修改代理等级')
    {
        if (empty($member_id) || $change_agent_level < 0) {
            return ['code' => 0, 'msg' => '参数有误'];
        }

        $level = model\AgentLevel::where('level', $change_agent_level)->find();

        if ($change_agent_level > 0 && !$level) {
            return ['code' => 0, 'msg' => '等级不存在'];
        }

        $member = model\Member::find($member_id);

        if (!$member) {
            return ['code' => 0, 'msg' => '用户不存在'];
        }

        if ($member['agent_level'] == $change_agent_level) {
            return ['code' => 0, 'msg' => '新旧代理等级一样'];
        }

        $res = model\Member::where(['id' => $member_id])->update(['agent_level' => $change_agent_level]);
        if ($res) {
            model\MemberLog::create([
                'member_id' => $member_id,
                'desc' => $desc,
                'change' => $level ? $level['name'] : '--',
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            return ['code' => 1, 'msg' => '修改代理等级成功'];
        }

        return ['code' => 0, 'msg' => '等级未能更新'];
    }

    /**
     * 获取指定的上级代理用户
     *
     * @param [type] $agent_id
     * @return void
     */
    protected function getLeader($leader_id)
    {
        $leader = model\Member::find($leader_id); //其上级可分成
        if (!$leader) //代理不存在或代理取消
        {
            return false;
        }
        return $leader;
    }

    /**
     * Undocumented function
     *
     * @param array $member
     * @return void
     */
    public function getLink($member)
    {
        $upperMembers = $this->getUpperMembers($member, array(), 999);
        $leader = $this->getLeader($member['first_leader']);
        $count = count($upperMembers);
        if ($count > 0) {
            $str = ",";
            for ($i = $count - 1; $i >= 0; $i -= 1) {
                if ($upperMembers[$i]['id'] != $member['id']) {
                    $str .= $upperMembers[$i]['id'] . ",";
                }
            }
            $str .= $member['id'] . ",";

            $save = ['relation' => $str, 'second_leader' => $leader ? $leader['first_leader'] : 0, 'third_leader' => $leader ? $leader['second_leader'] : 0];
            model\Member::where(['id' => $member['id']])->update($save);
        } else {
            model\Member::where(['id' => $member['id']])->update(['relation' => "," . $member['id'] . ","]);
        }
    }

    public function getJianjie($member)
    {
        if ($member['first_leader'] == 0) {
            $member['relation'] = "," . $member['id'] . ",";

            model\Member::where(['id' => $member['id']])->update(['relation' => $member['relation'], 'second_leader' => 0, 'third_leader' => 0]);
        }

        $lowMembers = model\Member::where(['first_leader' => $member['id']])->select();

        foreach ($lowMembers as $lower) {
            $lower['relation_ship'] = $lower['relation'] . $lower['user_id'] . ",";
            $lower['second_leader'] = $member['first_leader'];
            $lower['third_leader'] = $member['second_leader'];

            model\Member::where(['id' => $lower['id']])->update([
                'relation' => $member['relation'] . $lower['user_id'] . ",",
                'second_leader' => $member['first_leader'], 'third_leader' => $member['second_leader'],
            ]);

            $this->getJianjie($lower);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $member 用户
     * @param array $list 已获取的上级列表，第一次是传空数组
     * @param integer $limit 层级限制
     * @return array
     */
    public function getUpperMembers($member, $list, $limit = 999)
    {
        foreach ($list as $li) {
            if ($li['id'] == $member['id']) {
                Log::alert("死循环:" . json_encode($member));
                return $list;
            }
        }
        if (count($list) >= $limit) {
            return $list;
        }
        //if ($user['agent_level'] >= 1) {
        $list[] = $member;
        //}

        if ($member['first_leader'] == 0 || $member['first_leader'] == $member['id']) {
            return $list;
        }

        $leader = $this->getLeader($member['first_leader']);

        if ($leader) {
            $list = $this->getUpperMembers($leader, $list, $limit);
        }

        return $list;
    }
}
