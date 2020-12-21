CREATE TABLE IF NOT EXISTS `__PREFIX__agent_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '名称',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员等级';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播ID',
  `title` varchar(55) NOT NULL DEFAULT '' COMMENT '轮播名称',
  `position_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '位置',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '轮播图片',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='CMS轮播';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '类目名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '栏目类型',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `deep` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '链接',
  `path` varchar(55) NOT NULL DEFAULT '' COMMENT '链接',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文章栏目';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `title` varchar(55) NOT NULL DEFAULT '' COMMENT '文章名称',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  `author` varchar(32) NOT NULL DEFAULT '' COMMENT '作者',
  `source` varchar(32) NOT NULL DEFAULT '' COMMENT '来源',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '热门',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标签',
  `keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `attachment` varchar(255) NOT NULL DEFAULT '' COMMENT '附件',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` text COMMENT '文章内容',
  `publish_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '发布时间',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
  `video` varchar(255) NOT NULL DEFAULT '' COMMENT 'video',
  `create_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加人',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文章内容';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '位置ID',
  `name` varchar(55) NOT NULL COMMENT '类目名称',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `start_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '开始时间',
  `end_time` datetime NOT NULL DEFAULT '2030-01-01 00:00:00' COMMENT '结束时间',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文章栏目';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '轮播名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS标签';

CREATE TABLE IF NOT EXISTS `__PREFIX__delivery_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '发货单ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `consignee` varchar(64) NOT NULL DEFAULT '' COMMENT '收货人',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '联系手机',
  `province` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '省ID',
  `city` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '市ID',
  `area` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '区ID',
  `town` bigint(20) unsigned DEFAULT '0' COMMENT '街道ID',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `shipping_code` varchar(32) DEFAULT '' COMMENT '物流code',
  `shipping_name` varchar(64) DEFAULT '' COMMENT '快递名称',
  `shipping_price` decimal(10,2) DEFAULT '0.00' COMMENT '运费',
  `invoice_no` varchar(255) NOT NULL DEFAULT '' COMMENT '物流单号',
  `note` varchar(500) DEFAULT '' COMMENT '管理员添加的备注信息',
  `best_time` datetime DEFAULT '1970-01-01 08:00:00' COMMENT '友好收货时间',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '创建时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `admin_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商家id',
  `last_check_time` datetime DEFAULT '1970-01-01 08:00:00' COMMENT '最后查询时间',
  `shipping_result` text COMMENT '查询结果',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `idx_province` (`province`),
  KEY `idx_city` (`city`),
  KEY `idx_area` (`area`),
  KEY `idx_admin_group_id` (`admin_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='发货单';

CREATE TABLE IF NOT EXISTS `__PREFIX__member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '登录账号',
  `nickname` varchar(20) NOT NULL DEFAULT '' COMMENT '昵称',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '邮件',
  `password` varchar(55) NOT NULL DEFAULT '' COMMENT '密码',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `age` tinyint(3) unsigned NOT NULL DEFAULT '18' COMMENT '年龄',
  `gender` varchar(5) NOT NULL DEFAULT '' COMMENT '性别',
  `province` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `area` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '地区',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `token` varchar(55) NOT NULL DEFAULT '' COMMENT 'token',
  `last_login_time` datetime DEFAULT '1970-01-01 08:00:00' COMMENT '最后登录时间',
  `last_login_ip` varchar(55) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `agent_level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '代理等级',
  `points` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '余额',
  `commission` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '佣金',
  `spend_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计消费',
  `openid` varchar(75) NOT NULL DEFAULT '' COMMENT 'openid',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `first_leader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级',
  `second_leader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上上级',
  `third_leader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上上上级',
  `relation` varchar(1000) DEFAULT '' COMMENT '关系',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '注册时间',
  `update_time` varchar(55) NOT NULL DEFAULT '' COMMENT '更新时间',
  `agent_img` varchar(255) DEFAULT '' COMMENT '推广图片',
  `salt` varchar(10) DEFAULT '' COMMENT '盐',
  `errors` tinyint(3) unsigned DEFAULT '0' COMMENT '登录错误',
  `pay_password` varchar(60) DEFAULT '' COMMENT '支付密码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_username` (`username`),
  UNIQUE KEY `unq_mobile` (`mobile`),
  KEY `idx_level` (`level`),
  KEY `idx_first_leader` (`first_leader`),
  KEY `idx_second_leader` (`second_leader`),
  KEY `idx_third_leader` (`third_leader`),
  KEY `idx_agent_level` (`agent_level`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员';

CREATE TABLE IF NOT EXISTS `__PREFIX__member_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人id',
  `points` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '积分',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '佣金',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购物订单id',
  `tag` varchar(55) NOT NULL DEFAULT '' COMMENT '标记',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='账户流水';

CREATE TABLE IF NOT EXISTS `__PREFIX__member_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `consignee` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人',
  `province` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `area` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '县区',
  `town` bigint(20) DEFAULT '0' COMMENT '乡镇',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `mobile` varchar(60) NOT NULL DEFAULT '' COMMENT '手机',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` varchar(55) NOT NULL DEFAULT '' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='收货地址';

CREATE TABLE IF NOT EXISTS `__PREFIX__member_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '名称',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `points` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分条件',
  `spend_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计消费',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员等级';

CREATE TABLE IF NOT EXISTS `__PREFIX__member_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `desc` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
  `change` varchar(55) NOT NULL DEFAULT '' COMMENT '等级变化',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '变动时间',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员日志';

CREATE TABLE IF NOT EXISTS `__PREFIX__member_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '充值单号',
  `account` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `pay_code` varchar(20) DEFAULT '' COMMENT '支付方式',
  `pay_status` tinyint(1) DEFAULT '0' COMMENT '充值状态0:待支付 1:充值成功 2:交易关闭',
  `pkg_id` int(10) DEFAULT '0' COMMENT 'pkg',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `transaction_id` varchar(80) DEFAULT NULL COMMENT 'transaction_id',
  `create_time` datetime DEFAULT NULL COMMENT '充值时间',
  `use_child_account` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '使用子账号',
  `goods_method` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '获取商品方式',
  `buy_num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '充值份数',
  `first_leader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='充值记录';

CREATE TABLE IF NOT EXISTS `__PREFIX__shipping_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `first_weight` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '首重',
  `money` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '首重费',
  `second_weight` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '续重',
  `add_money` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '续重费',
  `com_codes` varchar(255) NOT NULL DEFAULT '' COMMENT '快递公司',
  `enable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='区域运费';

CREATE TABLE IF NOT EXISTS `__PREFIX__shipping_area_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `com_codes` varchar(255) NOT NULL DEFAULT '' COMMENT '快递公司',
  `area_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '区域id',
  `province` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `area` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '县区',
  `town` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '乡镇',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='区域运费条目';

CREATE TABLE IF NOT EXISTS `__PREFIX__shipping_com` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(55) NOT NULL DEFAULT '' COMMENT '编码',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '启用',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_name` (`name`),
  UNIQUE KEY `unq_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='快递公司';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_brand` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '品牌ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '品牌名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '品牌类型',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `deep` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '链接',
  `path` varchar(55) NOT NULL DEFAULT '' COMMENT '链接',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品品牌';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车id',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `session_id` varchar(128) NOT NULL DEFAULT '' COMMENT 'session',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_spu` varchar(60) NOT NULL DEFAULT '' COMMENT '商品货号',
  `goods_name` varchar(120) NOT NULL DEFAULT '' COMMENT '商品名称',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `sale_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '本店价',
  `member_price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '会员价',
  `goods_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '购买数量',
  `spec_key` varchar(64) NOT NULL DEFAULT '' COMMENT '商品规格key 对应tp_spec_goods_price 表',
  `spec_key_name` varchar(64) DEFAULT '' COMMENT '商品规格组合名称',
  `prom_type` tinyint(1) DEFAULT '0' COMMENT '0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠',
  `prom_id` int(11) DEFAULT '0' COMMENT '活动id',
  `goods_sku` varchar(128) DEFAULT '' COMMENT '产品sku',
  `weight` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '商品重量克为单位',
  `tag` varchar(20) DEFAULT '' COMMENT '标记',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='购物车';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '类目名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '栏目类型',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `deep` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '链接',
  `path` varchar(55) NOT NULL DEFAULT '' COMMENT '链接',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品分类';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_coupon_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `coupon_type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券 对应coupon_type表id',
  `card_type` smallint(1) unsigned NOT NULL DEFAULT '1' COMMENT '券的使用类型(1折扣券/2现金券)',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发放类型 1 按订单发放 2 注册 3 邀请 4 按用户发放',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `use_time` datetime DEFAULT NULL COMMENT '使用时间',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '优惠券兑换码',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '发放时间',
  `get_time` date DEFAULT NULL COMMENT '领取日期',
  `no_` int(10) unsigned DEFAULT '0' COMMENT '编号',
  `status` smallint(1) DEFAULT '1' COMMENT '状态',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `ctype` (`card_type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `cid` (`coupon_type_id`) USING BTREE,
  KEY `sn` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='优惠券';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_coupon_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '优惠券名字',
  `card_type` smallint(1) NOT NULL DEFAULT '1' COMMENT '券的使用类型(1折扣券/2现金券)',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发放类型 0面额模板1 按用户发放 2 注册 3 邀请 4 线下发放',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券金额',
  `condition` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用条件',
  `create_num` int(11) unsigned DEFAULT '0' COMMENT '累计发放数量',
  `send_num` int(11) unsigned DEFAULT '0' COMMENT '已发放数量',
  `use_num` int(11) unsigned DEFAULT '0' COMMENT '已使用数量',
  `get_num` int(11) unsigned DEFAULT '0' COMMENT '已领取数量',
  `del_num` int(11) unsigned DEFAULT '0' COMMENT '已删除',
  `send_start_time` date DEFAULT NULL COMMENT '发放开始时间',
  `send_end_time` date DEFAULT NULL COMMENT '发放结束时间',
  `use_start_time` date DEFAULT NULL COMMENT '使用开始时间',
  `use_end_time` date DEFAULT NULL COMMENT '使用结束时间',
  `create_time` datetime DEFAULT NULL COMMENT '添加时间',
  `status` smallint(1) unsigned DEFAULT '1' COMMENT '状态',
  `ally_id` int(11) unsigned DEFAULT '0' COMMENT '加盟商id',
  `for_goods` varchar(555) DEFAULT NULL COMMENT '设置限定只能这些商品使用',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_card_type` (`card_type`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='优惠券类型';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '产品名称',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  `brand_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属品牌',
  `sale_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '销售价格',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `cost_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '热门',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '产品标签',
  `keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `spu` varchar(100) NOT NULL DEFAULT '' COMMENT 'SPU编码',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `images` varchar(1000) NOT NULL DEFAULT '' COMMENT '多图',
  `video` varchar(255) NOT NULL DEFAULT '' COMMENT '附件',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` text COMMENT '产品详情',
  `publish_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '上架时间',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `on_sale` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '上架状态',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
  `weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品重量克为单位',
  `sales_sum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售量',
  `create_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加人',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `admin_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商家',
  `share_commission` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '分销佣金',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `idx_admin_group_id` (`admin_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_attr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '名称',
  `value` varchar(1000) NOT NULL DEFAULT '' COMMENT '值',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品属性';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_spec` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规格ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '名称',
  `value` varchar(1000) NOT NULL DEFAULT '' COMMENT '值',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品规格';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_spec_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '价格ID',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `spec_ids` varchar(55) NOT NULL DEFAULT '' COMMENT '规格ID',
  `spec_key` varchar(128) NOT NULL DEFAULT '' COMMENT '商品规格key',
  `spec_key_name` varchar(128) NOT NULL DEFAULT '' COMMENT '规格对应的中文名字',
  `data` varchar(255) NOT NULL DEFAULT '' COMMENT '规格值ID',
  `sku` varchar(55) NOT NULL DEFAULT '' COMMENT 'SKU编码',
  `sale_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '销售价格',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `spec_key` (`spec_key`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品规格';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_spec_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '价格ID',
  `spec_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '规格ID',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `value` varchar(55) NOT NULL DEFAULT '' COMMENT '值',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `model_id` (`spec_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='规格值';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '订单sn',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `consignee` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人',
  `province` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `area` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '县区',
  `town` bigint(20) DEFAULT '0' COMMENT '乡镇',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `mobile` varchar(60) NOT NULL DEFAULT '' COMMENT '手机',
  `shipping_code` varchar(32) NOT NULL DEFAULT '0' COMMENT '物流code',
  `shipping_name` varchar(120) NOT NULL DEFAULT '' COMMENT '物流名称',
  `invoice_title` varchar(256) DEFAULT NULL COMMENT '发票抬头',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总价',
  `shipping_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `use_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '使用余额',
  `use_points` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '使用积分',
  `points_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '使用积分抵多少钱',
  `coupon_card_type` tinyint(1) unsigned DEFAULT '0' COMMENT '优惠券类型:1折扣2现金',
  `coupon_price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '优惠券抵扣',
  `order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '应付款金额',
  `total_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总价',
  `order_prom_id` smallint(6) DEFAULT '0' COMMENT '活动id',
  `order_prom_amount` decimal(10,2) DEFAULT '0.00' COMMENT '活动优惠金额',
  `discount` decimal(10,2) DEFAULT '0.00' COMMENT '价格调整',
  `user_note` varchar(255) DEFAULT NULL COMMENT '用户备注',
  `admin_note` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `has_distribut` tinyint(1) DEFAULT '0' COMMENT '是否已分成0未分成1已分成',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态',
  `pay_code` varchar(32) NOT NULL DEFAULT '' COMMENT '支付code',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发货状态',
  `transaction_id` varchar(80) DEFAULT NULL COMMENT '支付机构流水号',
  `shipping_time` datetime DEFAULT NULL COMMENT '最后新发货时间',
  `confirm_time` datetime DEFAULT NULL COMMENT '收货确认时间',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `address_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收货地址id',
  `use_commission` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '使用蜜豆',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `member_id` (`member_id`),
  KEY `order_status` (`order_status`),
  KEY `shipping_status` (`shipping_status`),
  KEY `pay_status` (`pay_status`),
  KEY `shipping_code` (`shipping_code`),
  KEY `pay_code` (`pay_code`),
  KEY `province` (`province`),
  KEY `city` (`city`),
  KEY `area` (`area`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品订单';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order_action` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_sn` varchar(30) NOT NULL COMMENT '订单编号',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '操作人,0为用户自己',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配送状态',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态',
  `action_note` varchar(255) NOT NULL DEFAULT '' COMMENT '操作备注',
  `status_desc` varchar(255) DEFAULT NULL COMMENT '状态描述',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='订单记录';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id自增',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_name` varchar(120) NOT NULL DEFAULT '' COMMENT '视频名称',
  `goods_sn` varchar(60) NOT NULL DEFAULT '' COMMENT '商品货号',
  `goods_num` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '购买数量',
  `sale_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '产品价格',
  `member_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员支付价',
  `give_integral` decimal(10,2) DEFAULT '0.00' COMMENT '赠送积分',
  `spec_key` varchar(128) NOT NULL DEFAULT '' COMMENT '商品规格key',
  `spec_key_name` varchar(128) NOT NULL DEFAULT '' COMMENT '规格对应的中文名字',
  `is_comment` tinyint(1) DEFAULT '0' COMMENT '是否评价',
  `prom_type` tinyint(1) DEFAULT '0' COMMENT '0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠, ４ 砍价',
  `prom_id` int(10) DEFAULT '0' COMMENT '活动id',
  `is_send` tinyint(1) DEFAULT '0' COMMENT '0未发货，1已发货，2已换货，3已退货',
  `delivery_id` int(10) DEFAULT '0' COMMENT '发货单ID',
  `goods_sku` varchar(128) DEFAULT '' COMMENT '产品sku',
  `shipping_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `weight` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '商品重量克为单位',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='订单产品';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '标签名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品标签';

INSERT INTO `__PREFIX__shipping_com` (`id`, `name`, `code`, `enable`, `create_time`, `update_time`) VALUES
(1, '顺丰速运', 'SF', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(2, '百世快递', 'HTKY', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(3, '中通快递', 'ZTO', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(4, '申通快递', 'STO', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(5, '圆通速递', 'YTO', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(6, '韵达速递', 'YD', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(7, '邮政快递包裹', 'YZPY', 1, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(8, 'EMS', 'EMS', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(9, '天天快递', 'HHTT', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(10, '京东快递', 'JD', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(11, '优速快递', 'UC', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(12, '德邦快递', 'DBL', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(13, '宅急送', 'ZJS', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(14, '安捷快递', 'AJ', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(15, '阿里跨境电商物流', 'ALKJWL', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(16, '安迅物流', 'AX', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(17, '安邮美国', 'AYUS', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22'),
(18, '亚马逊物流', 'AMAZON', 0, '2020-06-26 14:17:22', '2020-06-26 14:17:22');
