CREATE TABLE IF NOT EXISTS `__PREFIX__cms_channel`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '类目名称',
  `full_name` varchar(255) NOT NULL DEFAULT '' COMMENT '完整类目名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '上级ID',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '栏目类型',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `deep` tinyint(2) unsigned NOT NULL DEFAULT 0 COMMENT '层级',
  `path` varchar(55) NOT NULL DEFAULT '' COMMENT '层级路径',
  `model_ids` varchar(55) NOT NULL DEFAULT '1' COMMENT '允许的模型',
  `channel_path` varchar(55) NOT NULL DEFAULT '[id]' COMMENT '栏目路径',
  `content_path` varchar(55) NOT NULL DEFAULT '[id]' COMMENT '内容路径',
  `extend_ids` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '附加其他栏目',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `is_show` tinyint(1) unsigned DEFAULT 1 COMMENT '是否显示',
  `is_navi` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '首页导航',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `order_by` varchar(55) NOT NULL DEFAULT '' COMMENT '排序方式',
  `pagesize` mediumint(8) unsigned NOT NULL DEFAULT 12 COMMENT '分页大小',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='栏目';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '内容ID',
  `title` varchar(125) NOT NULL DEFAULT '' COMMENT '栏目',
  `channel_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '所属分类',
	`model_id` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '模型id',
  `author` varchar(32) NOT NULL DEFAULT '' COMMENT '作者',
  `source` varchar(32) NOT NULL DEFAULT '' COMMENT '来源',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '推荐',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '热门',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '置顶',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '合集',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `publish_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '发布时间',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '是否显示',
  `click` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '点击量',
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加人',
  `reference_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '引用id',
  `mention_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '关联文章ids',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_detail`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `main_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '内容ID',
  `content` text DEFAULT NULL COMMENT '内容',
  `attachments` varchar(5000) NOT NULL DEFAULT '' COMMENT '附件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章详情';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_position`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '位置ID',
  `name` varchar(55) NOT NULL COMMENT '类目名称',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `start_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '开始时间',
  `end_time` datetime NOT NULL DEFAULT '2030-01-01 00:00:00' COMMENT '结束时间',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET=utf8 COMMENT = '文章栏目';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播ID',
  `title` varchar(55) NOT NULL DEFAULT '' COMMENT '轮播名称',
  `position_id` int(10) NOT NULL DEFAULT 0 COMMENT '位置',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '轮播图片',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '是否显示',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS轮播';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '标签名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '是否显示',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容标签';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '模板名称',
  `platform` varchar(25) NOT NULL DEFAULT '' COMMENT '平台类型',
  `view_path` varchar(25) NOT NULL DEFAULT '' COMMENT '模板基础路径',
  `prefix` varchar(25) NOT NULL DEFAULT '' COMMENT '生成路径前缀',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '是否启用',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模板';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `to_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '栏目/内容ID',
  `template_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '模板ID',
  `html_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '页面ID',
  `html_type` varchar(25) NOT NULL DEFAULT '' COMMENT '页面类型',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容模板页';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_template_html` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `template_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '模板ID',
	`to_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '内容ID',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT '页面名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '页面路径',
  `key` varchar(55) NOT NULL DEFAULT '' COMMENT 'key',
  `type` varchar(25) NOT NULL DEFAULT '' COMMENT '页面类型',
  `ext` varchar(25) NOT NULL DEFAULT '' COMMENT '后缀',
  `size` varchar(25) NOT NULL DEFAULT '' COMMENT '大小',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否为默认模板',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `version` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '版本号',
  `filectime` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '文件创建时间',
  `filemtime` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '文件编辑时间',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模板页面';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_model` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
	`name` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '名称',
  `sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
	`fields` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '字段',
	`create_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
	`update_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容模型';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_field` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
	`name` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '字段名称',
	`displayer_type` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '显示类型',
	`options` TEXT DEFAULT NULL COMMENT '选项',
  `length` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '长度',
	`numerc_scale` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '小数点',
	`data_type` VARCHAR(55) NULL DEFAULT '' COMMENT '数据类型',
	`default` VARCHAR(55) NULL DEFAULT '' COMMENT '默认值',
	`position` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '位置',
	`create_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
	`update_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容字段';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_model_field` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
	`model_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模型id',
	`name` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '字段名称',
  `comment` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '字段说明',
  `help` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '帮助信息',
	`displayer_type` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '显示类型',
	`position` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '位置',
	`rules` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '规则',
	`is_custom` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '是否为自定义字段',
	`create_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
	`update_time` DATETIME NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
	PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容模型字段';

INSERT INTO `__PREFIX__cms_template` (`id`, `name`, `platform`, `view_path`, `prefix`, `description`, `sort`, `is_open`, `create_time`, `update_time`) VALUES
(1, 'default', 'pc', 'default', '/', 'pc端默认模板', 5, 1, '2024-12-06 14:34:34', '2024-12-06 14:34:34');

INSERT INTO `__PREFIX__cms_channel` (`id`, `name`, `full_name`, `parent_id`, `logo`, `type`, `link`, `deep`, `path`, `model_ids`, `order_by`, `channel_path`, `content_path`, `description`, `keywords`, `extend_ids`, `is_show`, `is_navi`, `sort`, `pagesize`, `create_time`, `update_time`, `delete_time`) VALUES
(1, '关于', '关于', 0, '', 3, '', 1, ',0,', '1', '', '[id]', '[id]', '', '', '', 1, 0, 999, 12, '2024-12-06 14:34:34', '2024-12-06 15:21:43', NULL);

INSERT INTO `__PREFIX__cms_content` (`id`, `title`, `channel_id`, `author`, `source`, `is_recommend`, `is_hot`, `is_top`, `tags`, `keywords`, `link`, `logo`, `description`, `mention_ids`, `publish_time`, `sort`, `is_show`, `click`, `admin_id`, `reference_id`, `create_time`, `update_time`, `delete_time`) VALUES
(2, '联系我们', 1, '管理员', '默认分组', 0, 0, 0, '', '', '', '', '联系我们', '', '2024-12-09 22:10:17', 10, 1, 40, 0, 0, '2024-12-09 22:12:08', '2024-12-10 00:29:12', NULL),
(1, '关于我们', 1, '管理员', '默认分组', 0, 0, 0, '', '', '', '', '我们是共产主义接班人。', '', '2024-12-09 22:09:11', 5, 1, 20, 0, 0, '2024-12-09 22:10:01', '2024-12-10 00:28:41', NULL);

INSERT INTO `__PREFIX__cms_content_detail` (`id`, `main_id`, `content`, `attachments`) VALUES
(1, 1, '<p></p><p>我们是共产主义接班人。</p><p></p>', ''),
(2, 2, '<p></p><p>地址：中国.云南</p><p>电话：0871-1234567</p><p>Email：admin@tpext-cms.cn<br></p>', '');

INSERT INTO `__PREFIX__cms_position` (`id`, `name`, `logo`, `type`, `is_show`, `sort`, `start_time`, `end_time`, `create_time`, `update_time`) VALUES
(1, '首页焦点图', '', 1, 1, 5, '2024-12-06 00:00:00', '2025-12-06 00:00:00', '2024-12-06 14:50:27', '2024-12-06 14:50:27'),
(2, '底部友情链接', '', 1, 1, 10, '2024-12-06 00:00:00', '2025-12-06 00:00:00', '2024-12-06 14:50:45', '2024-12-06 14:50:45');

INSERT INTO `__PREFIX__cms_banner` (`id`, `title`, `position_id`, `description`, `image`, `link`, `sort`, `is_show`, `create_time`, `update_time`) VALUES
(1, '图片1', 1, '', '/theme/default/images/1.jpg', '#', 5, 1, '2024-12-08 15:21:05', '2024-12-08 15:39:59'),
(2, '图片2', 1, '', '/theme/default/images/2.jpg', '#', 10, 1, '2024-12-08 15:21:14', '2024-12-08 15:39:53'),
(3, '图片3', 1, '', '/theme/default/images/3.jpg', '#', 15, 1, '2024-12-08 15:38:38', '2024-12-08 15:39:49'),
(4, '百度', 2, '', '', 'https://www.baidu.com', 20, 1, '2024-12-09 21:41:07', '2024-12-09 21:41:46'),
(5, '新浪', 2, '', '', 'https://www.sina.com.cn', 25, 1, '2024-12-09 21:41:27', '2024-12-09 21:41:43'),
(6, '腾讯', 2, '', '', 'https://www.qq.com', 30, 1, '2024-12-09 21:42:07', '2024-12-09 21:42:07'),
(7, '网易', 2, '', '', 'https://www.163.com', 35, 1, '2024-12-09 21:42:28', '2024-12-09 21:42:28'),
(8, '淘宝', 2, '', '', 'https://www.taobao.com', 40, 1, '2024-12-09 21:42:46', '2024-12-09 21:42:50'),
(9, '京东', 2, '', '', 'https://www.jd.com', 45, 1, '2024-12-09 21:43:20', '2024-12-09 21:43:20'),
(10, 'bilibili', 2, '', '', 'https://www.bilibili.com', 50, 1, '2024-12-09 21:43:52', '2024-12-09 21:54:07'),
(11, '12306', 2, '', '', 'https://www.12306.cn', 55, 1, '2024-12-09 21:52:34', '2024-12-09 21:52:34'),
(12, '豆瓣网', 2, '', '', 'https://www.douban.com', 60, 1, '2024-12-09 21:52:57', '2024-12-09 21:54:11'),
(13, '人民网', 2, '', '', 'http://www.people.com.cn', 65, 1, '2024-12-09 21:54:00', '2024-12-09 21:54:00'),
(14, '新华网', 2, '', '', 'http://www.xinhuanet.com', 70, 1, '2024-12-09 21:54:28', '2024-12-09 21:54:28'),
(15, '喜马拉雅FM', 2, '', '', 'https://www.ximalaya.com', 75, 1, '2024-12-09 21:55:23', '2024-12-09 21:55:23');

INSERT INTO `__PREFIX__cms_tag` (`id`, `name`, `description`, `logo`, `sort`, `is_show`, `create_time`, `update_time`) VALUES
(1, '站长推荐', '', '', 5, 1, '2024-12-11 21:42:06', '2024-12-11 21:42:06'),
(2, '图集', '', '', 10, 1, '2024-12-11 21:43:18', '2024-12-11 21:43:18'),
(3, '视频', '', '', 15, 1, '2024-12-11 21:43:23', '2024-12-11 21:43:23'),
(4, '下载', '', '', 20, 1, '2024-12-11 21:43:28', '2024-12-11 21:43:28');

INSERT INTO `__PREFIX__cms_content_model` (`id`, `name`, `fields`, `create_time`, `update_time`, `sort`) VALUES
(1, '文章', 'author,source,is_recommend,is_hot,is_top,tags,description,keywords,link,logo,publish_time,sort,is_show,click,reference_id,mention_ids,real_name,content,attachments', '2024-12-13 14:20:52', '2024-12-14 18:02:54', 1),
(2, '图集', 'content,attachments', '2024-12-13 19:54:05', '2024-12-16 11:17:02', 2),
(3, '视频', 'logo,content,attachments', '2024-12-13 19:59:54', '2024-12-16 11:17:58', 3);

INSERT INTO `__PREFIX__cms_content_model_field` (`id`, `model_id`, `name`, `comment`, `help`, `displayer_type`, `position`, `rules`, `is_custom`, `create_time`, `update_time`) VALUES
(1, 1, 'author', '作者', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(2, 1, 'is_recommend', '推荐', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(3, 1, 'is_hot', '热门', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(4, 1, 'is_top', '置顶', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(5, 1, 'tags', '文章合集', '', 'auto', 'main_left', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:37:16'),
(6, 1, 'description', '摘要', '', 'auto', 'main_left', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(7, 1, 'keywords', '关键字', '', 'auto', 'main_left', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(8, 1, 'link', '跳转链接', '', 'auto', 'extend', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(9, 1, 'logo', '封面图', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(10, 1, 'publish_time', '发布时间', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(11, 1, 'sort', '排序', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(12, 1, 'is_show', '是否显示', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(13, 1, 'click', '点击量', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(14, 1, 'reference_id', '引用id', '', 'auto', 'main_right', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(15, 1, 'mention_ids', '关联文章ids', '', 'auto', 'extend', '', 0, '2024-12-13 22:21:06', '2024-12-16 12:27:27'),
(16, 1, 'content', '正文内容', '', 'editor', 'auto', 'required', 0, '2024-12-13 22:21:06', '2024-12-16 12:37:16'),
(17, 1, 'attachments', '附件', '', 'files', 'extend', '', 0, '2024-12-13 22:21:06', '2024-12-16 11:44:49'),
(18, 1, 'source', '来源', '', 'auto', 'main_right', '', 0, '2024-12-16 18:39:48', '2024-12-16 18:39:48'),
(19, 2, 'content', '图片描述', '每张图片对应一条文字描述，换行分隔', 'textarea', 'auto', '', 0, '2024-12-13 22:23:02', '2024-12-16 11:16:56'),
(20, 2, 'attachments', '图片', '', 'images', 'main_left', 'required', 0, '2024-12-13 22:23:02', '2024-12-16 11:15:39'),
(21, 3, 'content', '视频描述', '', 'editor', 'auto', '', 0, '2024-12-13 22:24:45', '2024-12-16 11:17:48'),
(22, 3, 'attachments', '视频', '', 'file', 'main_left', 'required', 0, '2024-12-13 22:24:45', '2024-12-16 11:15:51'),
(23, 3, 'logo', '封面图', '视频封面', 'auto', 'main_left', '', 0, '2024-12-16 11:17:48', '2024-12-16 11:39:49');

