

DROP TABLE IF EXISTS  `user_status`;
CREATE TABLE `user_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `status_key` varchar(32) NOT NULL COMMENT '类型 1弹幕',
  `status_value` varchar(32) NOT NULL COMMENT '值，尽量用负面的，可以被删掉',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2650 DEFAULT CHARSET=utf8 COMMENT='用户的状态，2表示是不是可以让别人进行个人页面的访问，3 表示禁止发布内容，4表示认证的状态，一般为没有认证';

DROP TABLE IF EXISTS  `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) DEFAULT NULL COMMENT '用户的 id',
  `nikename` varchar(120) DEFAULT NULL COMMENT '用户昵称',
  `gender` int(11) DEFAULT NULL COMMENT '用户性别 性别 1男 2女 3未知',
  `update_time` int(11) DEFAULT NULL COMMENT '更新的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4817 DEFAULT CHARSET=utf8mb4 COMMENT='用户的基本信息';

DROP TABLE IF EXISTS  `user_password`;
CREATE TABLE `user_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) DEFAULT NULL COMMENT '用户的 id',
  `pwd` varchar(96) DEFAULT NULL COMMENT '密码',
  `update_time` int(11) DEFAULT NULL COMMENT '更新',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8mb4 COMMENT='用户的密码表';

DROP TABLE IF EXISTS  `user_emblem`;
CREATE TABLE `user_emblem` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) NOT NULL COMMENT '用户的id',
  `login_type` int(11) NOT NULL COMMENT '登录身份\n1设备\n2手机\n3QQ\n4微博\n5微信',
  `login_value` varchar(96) NOT NULL COMMENT '对应的值',
  `create_time` int(11) NOT NULL COMMENT '绑定的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5312 DEFAULT CHARSET=utf8 COMMENT='用户的第三方登录标记，用于查找注册信息';

DROP TABLE IF EXISTS  `user_detail`;
CREATE TABLE `user_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) DEFAULT NULL COMMENT '用户的 id',
  `item_id` int(11) DEFAULT NULL COMMENT '商品的 id',
  `open_iid` char(24) DEFAULT NULL COMMENT '商品的唯一标识',
  `create_time` int(11) DEFAULT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3370 DEFAULT CHARSET=utf8mb4 COMMENT='用户的心愿清单';

DROP TABLE IF EXISTS  `user_avatar`;
CREATE TABLE `user_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `avatar` varchar(120) NOT NULL COMMENT '头像的地址',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '状态，默认1使用',
  `update_time` int(11) NOT NULL COMMENT '更新的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5459 DEFAULT CHARSET=utf8 COMMENT='用户的头像，可以保存五个包括现有的历史头像';

DROP TABLE IF EXISTS  `user_account`;
CREATE TABLE `user_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `create_time` int(11) NOT NULL COMMENT '注册时间',
  `create_channel` varchar(32) NOT NULL COMMENT '注册渠道',
  `create_os` int(11) NOT NULL COMMENT '设备，1安卓，2iOS',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5315 DEFAULT CHARSET=utf8 COMMENT='用户的账户注册信息表';

DROP TABLE IF EXISTS  `token_user`;
CREATE TABLE `token_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `logintime` int(11) NOT NULL COMMENT '登录时间',
  `deviceid` varchar(64) NOT NULL COMMENT '设备号',
  `clienttime` int(11) NOT NULL COMMENT '客户端时间',
  `requesttime` int(11) NOT NULL COMMENT '响应时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6428 DEFAULT CHARSET=utf8 COMMENT='用户的token';



