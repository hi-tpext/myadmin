<?php

namespace app\admin\controller;

use app\common\model\Member;
use app\common\model\MemberAddress as AddressModel;
use think\Controller;
use tpext\areacity\api\model\Areacity;
use tpext\builder\traits\HasBuilder;
use think\facade\Db;

/**
 * Undocumented class
 * @title 收货地址
 */
class Memberaddress extends Controller
{
    use HasBuilder;

    /**
     * Undocumented variable
     *
     * @var AddressModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new AddressModel;
        $this->pageTitle = '收货地址';

        $this->indexWith = ['member'];
    }

    /**
     * Undocumented function
     *
     * @title 下拉选择标签
     * @return mixed
     */
    public function selectPage($member_id = 0)
    {
        $selected = input('selected');

        $q = input('q');
        $page = input('page/d');

        $page = $page < 1 ? 1 : $page;
        $pagesize = 20;

        $where = [];

        if ($member_id) {
            $where[] = ['a.member_id', '=', $member_id];
        }
        if ($q) {
            $where[] = ['a.consignee|a.mobile|m.nickname|m.mobile', 'like', '%' . $q . '%'];
        }

        if ($selected) {
            $where[] = ['a.id', 'in', $selected];
        }

        $list = Db::name('member_address')->alias('a')->join('member m', 'a.member_id = m.id')->where($where)->field('a.*,m.nickname,m.mobile as m_mobile')->select();

        $data = [];

        foreach ($list as $li) {
            $text = '';

            $province = Areacity::where(['id' => $li['province']])->find();

            if ($province) {

                $text .= $province['ext_name'];
                $city = Areacity::where(['id' => $li['city']])->find();

                if ($city) {

                    $text .= ',' . $city['ext_name'];
                    $area = Areacity::where(['id' => $li['area']])->find();

                    if ($area) {

                        $text .= ',' . $area['ext_name'];

                        $town = Areacity::where(['id' => $li['town']])->find();
                        if ($town) {

                            $text .= ',' . $town['ext_name'];
                        }
                    }
                }
            }
            $data[] = [
                'id' => $li['id'],
                'text' => '[会员#' . $li['member_id'] . ',' . $li['nickname'] . ($li['m_mobile'] ? ',' . $li['m_mobile'] : '') . ']－收货人:'
                . $li['consignee'] . ',手机:' . $li['mobile'] . ',地址:' . $text . ',' . $li['address'],
            ];
        }

        return json(
            [
                'data' => $data,
                'has_more' => count($data) >= $pagesize,
            ]
        );
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];
        if (!empty($searchData['member_id'])) {
            $where[] = ['member_id', '=', $searchData['member_id']];
        }

        if (!empty($searchData['consignee'])) {
            $where[] = ['consignee', 'like', '%' . $searchData['consignee'] . '%'];
        }

        if (!empty($searchData['mobile'])) {
            $where[] = ['mobile', 'like', '%' . $searchData['mobile'] . '%'];
        }

        if (!empty($searchData['address'])) {
            $where[] = ['address', 'like', '%' . $searchData['address'] . '%'];
        }

        if (!empty($searchData['province'])) {
            $where[] = ['province', '=', $searchData['province']];

            if (!empty($searchData['city'])) {
                $where[] = ['city', '=', $searchData['city']];

                if (!empty($searchData['area'])) {
                    $where[] = ['area', '=', $searchData['area']];
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

        $search->select('member_id', '会员')->dataUrl(url('/admin/member/selectPage'));
        $search->text('consignee', '收货人')->maxlength(20);
        $search->text('mobile', '手机号')->maxlength(20);
        $search->text('address', '地址');

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
        $table->show('member.nickname', '会员');
        $table->text('consignee', '收货人')->autoPost()->getWrapper()->addStyle('width:150px');
        $table->show('mobile', '手机号')->getWrapper()->addStyle('width:150px');
        $table->show('pcat', '省市区');
        $table->text('address', '地址')->autoPost();
        $table->switchBtn('is_default', '默认地址')->autoPost()->addStyle('width:150px');
        $table->show('create_time', '添加时间')->getWrapper()->addStyle('width:150px');
        $table->show('update_time', '更新时间')->getWrapper()->addStyle('width:150px');
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

        $member_id = input('member_id/d');

        if ($isEdit) {
            $member_id = $data['member_id'];
        }

        if ($member_id) {
            $member = Member::find($member_id);
            if (!$member) {
                return $this->builder()->layer()->close(0, '用户不存在！id-' . $member_id);
            }
            $form->show('id_name', '会员')->value($member['id'] . '#' . $member['nickname']);
            $form->hidden('member_id')->value($member_id);
        } else {
            $form->select('member_id', '会员')->dataUrl(url('/admin/member/selectPage'))->size(2, 4)->required();
        }

        $selectP = $selectC = $selectA = $selectT = [];

        if ($isEdit) {
            $selectP = Areacity::where(['id' => $data['province']])->field('id,ext_name')->select();
            $selectC = Areacity::where(['id' => $data['city']])->field('id,ext_name')->select();
            $selectA = Areacity::where(['id' => $data['area']])->field('id,ext_name')->select();
            $selectT = Areacity::where(['id' => $data['town']])->field('id,ext_name')->select();
        }

        $form->text('consignee', '收货人')->required()->maxlength(20)->size(2, 4)->beforSymbol('<i class="mdi mdi-contacts"></i>');
        $form->text('mobile', '电话')->required()->maxlength(11)->size(2, 4)->beforSymbol('<i class="mdi mdi-cellphone-iphone"></i>');

        $form->fields('省/市/区/街道')->fullSize(2)->required();
        $form->select('province', ' ', 3)->size(0, 12)->showLabel(false)->optionsData($selectP, 'ext_name')->dataUrl(url('api/areacity/province'), 'ext_name')->withNext(
            $form->select('city', ' ', 3)->size(0, 12)->showLabel(false)->optionsData($selectC, 'ext_name')->dataUrl(url('api/areacity/city'), 'ext_name')->withNext(
                $form->select('area', ' ', 3)->size(0, 12)->showLabel(false)->optionsData($selectA, 'ext_name')->dataUrl(url('api/areacity/area'), 'ext_name')->withNext(
                    $form->select('town', ' ', 3)->size(0, 12)->showLabel(false)->optionsData($selectT, 'ext_name')->dataUrl(url('api/areacity/town'), 'ext_name')
                )
            )
        );
        $form->fieldsEnd();

        $form->textarea('address', '详细地址')->required();

        $form->switchBtn('is_default', '默认地址')->default(0);

        if ($isEdit) {
            $form->show('create_time', '注册时间');
            $form->show('update_time', '修改时间');
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
            'member_id',
            'consignee',
            'mobile',
            'province',
            'city',
            'area',
            'town',
            'address',
            'is_default',
        ], 'post');

        $result = $this->validate($data, [
            'member_id|会员' => 'require',
            'consignee|收货人' => 'require',
            'mobile|电话' => 'require|number|min:11',
            'province|省' => 'require|number',
            'city|市' => 'require|number',
            'area|区' => 'require|number',
            'town|街道' => 'number',
            'address|详细地址' => 'require',
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}
