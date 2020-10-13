<?php

namespace app\admin\controller;

use app\common\logic\MemberLogic;
use app\common\model;
use think\Controller;
use tpext\builder\traits\HasBuilder;

/**
 * Undocumented class
 * @title 会员管理
 */
class Member extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var model\Member
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new model\Member;
        $this->pageTitle = '会员管理';
        $this->enableField = 'status';
        $this->pagesize = 8;

        $this->selectTextField = '{id}#{nickname}({mobile})';
        $this->selectFields = 'id,nickname,mobile';
        $this->selectSearch = 'username|nickname|mobile';
    }

    protected function filterWhere()
    {
        $searchData = request()->post();

        $where = [];

        if (!empty($searchData['id'])) {
            $where[] = ['id', 'eq', $searchData['id']];
        }

        if (!empty($searchData['username'])) {
            $where[] = ['username', 'like', '%' . $searchData['username'] . '%'];
        }
        if (!empty($searchData['nickname'])) {
            $where[] = ['nickname', 'like', '%' . $searchData['nickname'] . '%'];
        }
        if (!empty($searchData['mobile'])) {
            $where[] = ['mobile', 'like', '%' . $searchData['mobile'] . '%'];
        }
        if (!empty($searchData['first_leader'])) {
            $where[] = ['first_leader', 'eq', $searchData['first_leader']];
        }
        if (!empty($searchData['team_leader'])) {
            $where[] = ['relation', 'like', '%,' . $searchData['team_leader'] . ',%'];
            $where[] = ['id', 'neq', $searchData['team_leader']];
        }

        if (isset($searchData['status']) && $searchData['status'] != '') {
            $where[] = ['status', 'eq', $searchData['status']];
        }
        if (isset($searchData['level']) && $searchData['level'] != '') {
            $where[] = ['level', 'eq', $searchData['level']];
        }
        if (isset($searchData['agent_level']) && $searchData['agent_level'] != '') {
            $where[] = ['agent_level', 'eq', $searchData['agent_level']];
        }
        if (!empty($searchData['province'])) {
            $where[] = ['province', 'eq', $searchData['province']];
            if (!empty($searchData['city'])) {
                $where[] = ['city', 'eq', $searchData['city']];
                if (!empty($searchData['area'])) {
                    $where[] = ['area', 'eq', $searchData['area']];
                }
            }
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

        $search->text('id', '会员id')->maxlength(20);
        $search->text('username', '账号')->maxlength(20);
        $search->text('nickname', '昵称')->maxlength(20);
        $search->text('mobile', '手机号')->maxlength(20);
        $search->select('first_leader', '上级会员')->dataUrl(url('/admin/member/selectPage'));
        $search->select('team_leader', '团队领导')->dataUrl(url('/admin/member/selectPage'));

        $search->select('level', '等级')->optionsData(model\MemberLevel::order('level')->select(), 'name', 'level')->afterOptions([0 => '普通会员']);
        $search->select('agent_level', '代理等级')->optionsData(model\AgentLevel::order('level')->select(), 'name', 'level')->afterOptions([0 => '普通会员']);
        $search->select('status', '状态')->options([0 => '禁用', 1 => '正常']);
        $search->select('province', '省份')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $search->select('city', '城市')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $search->select('area', '地区')->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;

        $table->show('id', 'ID');
        $table->image('avatar', '头像')->thumbSize(50, 50)->default('/static/images/touxiang.png');
        $table->show('username', '账号');
        $table->text('nickname', '昵称')->autoPost()->getWrapper()->addStyle('width:130px');
        $table->show('mobile', '手机号')->getWrapper()->addStyle('width:100px');
        $table->match('gender', '性别')->options([1 => '男', 2 => '女', 0 => '未知'])->getWrapper()->addStyle('width:50px');
        $table->show('level_name', '等级');
        $table->show('agent_level_name', '代理等级');
        $table->show('leader', '上级用户');
        $table->show('money', model\MemberAccount::$types['money']);
        $table->show('points', model\MemberAccount::$types['points']);
        $table->show('commission', model\MemberAccount::$types['commission']);

        $table->show('pca', '省市区');
        $table->switchBtn('status', '状态')->default(1)->autoPost()->getWrapper()->addStyle('width:60px');
        $table->show('last_login_time', '最近登录')->getWrapper()->addStyle('width:150px');
        $table->show('create_time', '注册时间')->getWrapper()->addStyle('width:150px');

        $table->sortable('id,sort,money,points,commission,last_login_time');

        $table->getToolbar()
            ->btnAdd()
            ->btnEnableAndDisable('启用', '禁用')
            ->btnRefresh();

        $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnLink('account', url('/admin/memberaccount/add', ['member_id' => '__data.pk__']), '', 'btn-success', 'mdi-square-inc-cash');
    }

    /**
     * 构建表单
     *
     * @param boolean $isEdit
     * @param array $data
     */
    protected function buildForm($isEdit, &$data = [])
    {
        $form = $this->form;

        $form->tab('基本信息');
        $form->image('avatar', '头像')->thumbSize(50, 50);
        $form->text('username', '账号')->required()->maxlength(20);
        $form->text('nickname', '昵称')->required()->maxlength(20);
        $form->text('mobile', '手机号')->maxlength(11);
        $form->text('email', '邮件')->maxlength(60);
        $form->radio('gender', '性别')->options([0 => '未知', 1 => '男', 2 => '女'])->default(0);
        $form->number('age', '年龄')->max(100)->min(1)->default(18);

        if ($isEdit) {
            $form->show('points', model\MemberAccount::$types['points']);
            $form->show('money', model\MemberAccount::$types['money']);
            $form->show('commission', model\MemberAccount::$types['commission']);
        }

        $form->tab('其他信息');
        $form->fields('省/市/区');
        $form->select('province', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $form->select('city', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $form->select('area', ' ', 4)->size(0, 12)->showLabel(false)->dataUrl(url('api/areacity/area'), 'ext_name')
            )
        );
        $form->fieldsEnd();

        $form->textarea('remark', '备注')->maxlength(255);
        $form->switchBtn('status', '状态')->default(1);

        $levels = model\MemberLevel::order('level')->field('name,level')->select();
        $agentLevels = model\AgentLevel::order('level')->field('name,level')->select();

        if ($isEdit) {
            $form->match('level', '等级')->optionsData($levels, 'name', 'level');
            $form->select('change_level', '改变等级')->optionsData($levels, 'name', 'level')->afterOptions([0 => '普通会员']);

            $form->show('last_login_ip', '最近登录IP')->default('-');

            if (session('admin_id') == 1) {
                $form->show('openid', 'openid')->default('-');
            }

            $form->show('last_login_time', '最近登录时间');
            $form->show('create_time', '注册时间');
            $form->show('update_time', '修改时间');
        }

        if ($isEdit) {
            $form->tab('分销关系');
            if ($isEdit == 1) {
                $form->textarea('change_desc', '操作备注')->help('改变上级/改变代理等级操作备注')->maxlength(400);
            }
            $form->show('leader', '上级', );
            if ($isEdit == 1) {
                $form->select('change_leader_id', '改变上级')->dataUrl(url('/admin/member/selectPage'));
            }
            $form->match('agent_level', '代理等级')->optionsData($agentLevels, 'name', 'level');
            if ($isEdit == 1) {
                $form->select('change_agent_level', '改变代理等级')->optionsData($agentLevels, 'name', 'level')->afterOptions([0 => '普通会员']);
            }
            $logic = new MemberLogic;
            $upmembers = $logic->getUpperMembers($data, []);
            $names = [];
            foreach ($upmembers as $m) {
                $names[] = $m['id'] . '#' . $m['nickname'] . '[' . $m['agent_level_name'] . ']';
            }
            $names = array_reverse($names);

            $form->raw('relation_text', '推荐关系图')->value('' . implode('&nbsp;<span style="color:red;">=></span>&nbsp;', $names) . '');
        } else {
            $form->select('level', '等级')->optionsData($levels, 'name', 'level')->afterOptions([0 => '普通会员'])->default(0);
            $form->select('agent_level', '代理等级')->optionsData($agentLevels, 'name', 'level')->afterOptions([0 => '非代理'])->default(0);
        }
    }

    /**
     * 保存数据
     *
     * @param integer $id
     * @return void
     */
    private function save($id = 0)
    {
        $data = request()->only([
            'avatar',
            'username',
            'nickname',
            'mobile',
            'email',
            'gender',
            'age',
            'change_level',
            'level',
            'province',
            'city',
            'area',
            'status',
            'remark',
            'change_leader_id',
            'agent_level',
            'change_agent_level',
            'change_desc',
        ], 'post');

        $result = $this->validate($data, [
            'username|账号' => 'require',
            'nickname|昵称' => 'require',
            'mobile|手机号' => 'mobile',
            'change_level|改变等级' => 'number',
            'change_agent_level|改变代理等级' => 'number',
            'level|等级' => 'number',
            'age|年龄' => 'number',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        if ($data['mobile'] && $exist = $this->dataModel->where(['mobile' => $data['mobile']])->find()) {
            if ($id) {
                if ($exist['id'] != $id) {
                    $this->error('手机号未能修改，已被占用');
                }
            } else {
                $this->error('手机号已被占用');
            }
        }

        $logic = new MemberLogic;

        if ($id > 0 && isset($data['change_leader_id']) && $data['change_leader_id'] > 0) {
            $res = $logic->changeLeader($id, $data['change_leader_id'], '修改上级' . ($data['change_desc'] ? '，' . $data['change_desc'] : ''));

            if ($res['code'] != 1) {
                $this->error($res['msg']);
            }
        }

        if ($id > 0 && isset($data['change_level']) && $data['change_level'] != '') {

            $res = $logic->changeLevel($id, $data['change_level']);

            if ($res['code'] != 1) {
                $this->error($res['msg']);
            }
        }

        if ($id > 0 && isset($data['change_agent_level']) && $data['change_agent_level'] != '') {

            $res = $logic->changeAgentLevel($id, $data['change_agent_level'], '修改代理等级' . ($data['change_desc'] ? '，' . $data['change_desc'] : ''));

            if ($res['code'] != 1) {
                $this->error($res['msg']);
            }
        }

        return $this->doSave($data, $id);
    }

    /**
     * Undocumented function
     * @title 分销关系
     * @return void
     */
    public function tree()
    {
        $member_id = input('member_id');
        $members = [];

        if ($member_id) {
            $members = $this->dataModel->where(['first_leader' => $member_id])->select();
        } else {
            $members = $this->dataModel->where(['first_leader' => 0])->select();
            //$members = [['id' => -1, 'nickname' => '用户数据过多，请输入上级id查询']];
        }

        foreach ($members as &$value) {
            $lowers = $this->dataModel->where(['first_leader' => $value['id']])->count() ?: 0;
            $value['lowers'] = $lowers;
        }

        $this->assign("members", $members);
        $this->assign("member_id", $member_id);
        return $this->fetch();
    }

    /**
     * Undocumented function
     * @title 分销关系查看下级
     * @return void
     */
    public function gets()
    {
        $id = input('get.id');
        $users = $this->dataModel->where(['first_leader' => $id])->select();
        $str = "<ul>";
        foreach ($users as $key => $value) {
            $lowers = $this->dataModel->where(['first_leader' => $value['id']])->count() ?: 0;
            $str = $str . "<li><span class='tree_span' data-id=" . $value['id'] . "><i class='icon-folder-open'></i>" . $value['id'] . ": " . $value['nickname'] . '(' . $lowers . ')' . "</span></li>";
        }
        $str = $str . "</ul>";
        return $str;
    }
}
