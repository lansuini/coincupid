CREATE TABLE `transfer_inout_9` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `player_uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `token` varchar(128) DEFAULT NULL COMMENT '用户TOKEN',
  `parent_bet_id` varchar(64) DEFAULT NULL COMMENT '母单号',
  `bet_id` varchar(64) DEFAULT NULL COMMENT '子单号',
  `bet_amount` int(11) DEFAULT NULL COMMENT '下注数目',
  `transfer_amount` int(11) DEFAULT NULL COMMENT '输赢',
  `transaction_id` varchar(128) DEFAULT NULL COMMENT '标识ID',
  `bill_type` int(11) DEFAULT NULL COMMENT '流水类型',
  `is_end` tinyint(4) DEFAULT NULL COMMENT '是否结束',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `game_id` int(11) DEFAULT NULL COMMENT '游戏ID',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `client_id` int(11) DEFAULT NULL COMMENT '渠道ID',
  `balanceBefore` int(11) DEFAULT NULL COMMENT '玩家交易前余额',
  `balanceAfter` int(11) DEFAULT NULL COMMENT '玩家交易后余额',
  `api_mode` tinyint(4) DEFAULT NULL COMMENT '钱包模式',
  UNIQUE KEY `idx_transaction_id` (`transaction_id`),
  KEY `id` (`id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `transfer_inout_backup_9` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `player_uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `token` varchar(128) DEFAULT NULL COMMENT '用户TOKEN',
  `parent_bet_id` varchar(64) DEFAULT NULL COMMENT '母单号',
  `bet_id` varchar(64) DEFAULT NULL COMMENT '子单号',
  `bet_amount` int(11) DEFAULT NULL COMMENT '下注数目',
  `transfer_amount` int(11) DEFAULT NULL COMMENT '输赢',
  `transaction_id` varchar(128) DEFAULT NULL COMMENT '标识ID',
  `bill_type` int(11) DEFAULT NULL COMMENT '流水类型',
  `is_end` tinyint(4) DEFAULT NULL COMMENT '是否结束',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `game_id` int(11) DEFAULT NULL COMMENT '游戏ID',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `client_id` int(11) DEFAULT NULL COMMENT '渠道ID',
  `balanceBefore` int(11) DEFAULT NULL COMMENT '玩家交易前余额',
  `balanceAfter` int(11) DEFAULT NULL COMMENT '玩家交易后余额',
  `api_mode` tinyint(4) DEFAULT NULL COMMENT '钱包模式',
  UNIQUE KEY `idx_transaction_id` (`transaction_id`),
  KEY `id` (`id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

RENAME TABLE `transfer_inout_backup` TO `transfer_inout_backup_8`;
RENAME TABLE `transfer_inout` TO `transfer_inout_8`;
insert into `transfer_inout_9` select * from `transfer_inout_8` where `client_id` = 9;
delete from `transfer_inout_8` where `client_id` = 9;

ALTER TABLE `transfer_inout_8` DROP INDEX `idx_client_id_create_time`;
ALTER TABLE `transfer_inout_backup_8` DROP INDEX `idx_client_id_create_time`;
ALTER TABLE `transfer_inout_8` ADD INDEX `idx_create_time` (`create_time`);
ALTER TABLE `transfer_inout_backup_8` ADD INDEX `idx_create_time` (`create_time`);