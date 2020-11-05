CREATE TABLE IF NOT EXISTS `__PREFIX__extdemo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '添加时间',
  `update_time` datetime NOT NULL DEFAULT '2020-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='后台权限表';

-- 默认权限
INSERT INTO `__PREFIX__extdemo` (`id`, `create_time`, `update_time`) VALUES
(1, '2020-11-11 11:11:11', '2020-11-11 11:11:11');