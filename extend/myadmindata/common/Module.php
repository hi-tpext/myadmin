<?php

namespace myadmindata\common;

use tpext\common\Module as baseModule;

class Module extends baseModule
{
    protected $version = '1.0.1';

    protected $name = 'myadmin.data';

    protected $title = 'myadmin演示';

    protected $description = '提供demo数据库脚本';

    protected $root = __DIR__ . '/../';

    /**
     * 后台菜单
     *
     * @var array
     */
    protected $menus = [
        [
            'title' => '产品管理',
            'sort' => 1,
            'url' => '#',
            'icon' => 'mdi mdi-store',
            'children' => [
                [
                    'title' => '产品管理',
                    'sort' => 1,
                    'url' => '/admin/shopgoods/index',
                    'icon' => 'mdi mdi-package-variant-closed',
                ],
                [
                    'title' => '产品分类',
                    'sort' => 2,
                    'url' => '/admin/shopcategory/index',
                    'icon' => 'mdi mdi-file-tree',
                ],
                [
                    'title' => '产品品牌',
                    'sort' => 3,
                    'url' => '/admin/shopbrand/index',
                    'icon' => 'mdi mdi-star-circle',
                ],
                [
                    'title' => '标签管理',
                    'sort' => 4,
                    'url' => '/admin/shoptag/index',
                    'icon' => 'mdi mdi-tag-outline',
                ],
            ],
        ],
        [
            'title' => '商城订单',
            'sort' => 2,
            'url' => '#',
            'icon' => 'mdi mdi-wallet-giftcard',
            'children' => [
                [
                    'title' => '订单管理',
                    'sort' => 1,
                    'url' => '/admin/shoporder/index',
                    'icon' => 'mdi mdi-view-list',
                ],
                [
                    'title' => '订单发货',
                    'sort' => 2,
                    'url' => '/admin/shopordershipping/index',
                    'icon' => 'mdi mdi-cube-send',
                ],
                [
                    'title' => '发货记录',
                    'sort' => 3,
                    'url' => '/admin/deliverylog/index',
                    'icon' => 'mdi mdi-calendar-text',
                ],
                [
                    'title' => '订单日志',
                    'sort' => 4,
                    'url' => '/admin/shoporderaction/index',
                    'icon' => 'mdi mdi-receipt',
                ],
                [
                    'title' => '快递公司',
                    'sort' => 5,
                    'url' => '/admin/shippingcom/index',
                    'icon' => 'mdi mdi-television-guide',
                ],
                [
                    'title' => '运费模板',
                    'sort' => 6,
                    'url' => '/admin/shippingarea/index',
                    'icon' => 'mdi mdi-book-open',
                ],
            ],
        ],
        [
            'title' => '内容管理',
            'sort' => 3,
            'url' => '＃',
            'icon' => 'mdi mdi-library-books',
            'children' => [
                [
                    'title' => '内容管理',
                    'sort' => 1,
                    'url' => '/admin/cmscontent/index',
                    'icon' => 'mdi mdi-book-open',
                ],
                [
                    'title' => '栏目管理',
                    'sort' => 2,
                    'url' => '/admin/cmscategory/index',
                    'icon' => 'mdi mdi-file-tree',
                ],
                [
                    'title' => '广告位置',
                    'sort' => 3,
                    'url' => '/admin/cmsposition/index',
                    'icon' => 'mdi mdi-bullhorn',
                ],
                [
                    'title' => '广告管理',
                    'sort' => 4,
                    'url' => '/admin/cmsbanner/index',
                    'icon' => 'mdi mdi-image-multiple',
                ],
                [
                    'title' => '标签管理',
                    'sort' => 5,
                    'url' => '/admin/cmstag/index',
                    'icon' => 'mdi mdi-tag-outline',
                ],
            ],
        ],
        [
            'title' => '会员管理',
            'sort' => 4,
            'url' => '#',
            'icon' => 'mdi mdi-account-circle',
            'children' => [
                [
                    'title' => '会员管理',
                    'sort' => 1,
                    'url' => '/admin/member/index',
                    'icon' => 'mdi mdi-account-box-outline',
                ],
                [
                    'title' => '代理等级',
                    'sort' => 2,
                    'url' => '/admin/agentlevel/index',
                    'icon' => 'mdi mdi-account-multiple-outline',
                ],
                [
                    'title' => '会员等级',
                    'sort' => 3,
                    'url' => '/admin/memberlevel/index',
                    'icon' => 'mdi mdi-trophy-award',
                ],
                [
                    'title' => '会员日志',
                    'sort' => 4,
                    'url' => '/admin/memberlog/index',
                    'icon' => 'mdi mdi-trophy-award',
                ],
                [
                    'title' => '收货地址',
                    'sort' => 5,
                    'url' => '/admin/memberaddress/index',
                    'icon' => 'mdi mdi-bulletin-board',
                ],
                [
                    'title' => '分销关系',
                    'sort' => 6,
                    'url' => '/admin/member/tree',
                    'icon' => 'mdi mdi-file-tree',
                ],
            ],
        ],
        [
            'title' => '资金管理',
            'sort' => 4,
            'url' => '#',
            'icon' => 'mdi mdi-cash',
            'children' => [
                [
                    'title' => '账户记录',
                    'sort' => 1,
                    'url' => '/admin/memberaccount/index',
                    'icon' => 'mdi mdi-square-inc-cash',
                ],
                [
                    'title' => '充值记录',
                    'sort' => 2,
                    'url' => '/admin/memberrecharge/index',
                    'icon' => 'mdi mdi-flag-variant',
                ],
            ],
        ],
        [
            'title' => '营销管理',
            'sort' => 5,
            'url' => '#',
            'icon' => 'mdi mdi-google-photos',
            'children' => [
                [
                    'title' => '优惠券',
                    'sort' => 1,
                    'url' => '/admin/shopcouponlist/index',
                    'icon' => 'mdi mdi-credit-card',
                ],
                [
                    'title' => '优惠券类型',
                    'sort' => 2,
                    'url' => '/admin/shopcoupontype/index',
                    'icon' => 'mdi mdi-cards',
                ],
            ],
        ],
        [
            'title' => '商家中心',
            'sort' => 6,
            'url' => '#',
            'icon' => 'mdi mdi-bulletin-board',
            'children' => [
                [
                    'title' => '订单发货',
                    'sort' => 1,
                    'url' => '/admin/shopordershipping/index',
                    'icon' => 'mdi mdi-cube-send',
                ],
                [
                    'title' => '发货记录',
                    'sort' => 2,
                    'url' => '/admin/deliverylog/index',
                    'icon' => 'mdi mdi-calendar-text',
                ],
            ],
        ],
    ];
}
