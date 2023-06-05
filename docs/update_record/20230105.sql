CREATE TABLE `server_request_log_8` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `uid` bigint(20) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `cost_time` int(11) DEFAULT NULL,
  `response` varchar(2048) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `params` varchar(2048) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `code` int(11) DEFAULT NULL,
  `args` varchar(2048) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_success` tinyint(1) DEFAULT '0',
  `admin_id` int(11) DEFAULT '0',
  `error_text` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `server_post_sub_log_8` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `transfer_reference` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_transfer_reference` (`transfer_reference`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `server_post_log_8` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trace_id` varchar(128) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `arg` varchar(1024) DEFAULT NULL,
  `return` varchar(1024) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(128) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `error_text` varchar(250) DEFAULT NULL,
  `cost_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE `server_request_log_9` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `uid` bigint(20) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `cost_time` int(11) DEFAULT NULL,
  `response` varchar(2048) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `params` varchar(2048) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `code` int(11) DEFAULT NULL,
  `args` varchar(2048) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_success` tinyint(1) DEFAULT '0',
  `admin_id` int(11) DEFAULT '0',
  `error_text` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `server_post_sub_log_9` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `transfer_reference` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_transfer_reference` (`transfer_reference`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `server_post_log_9` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trace_id` varchar(128) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `arg` varchar(1024) DEFAULT NULL,
  `return` varchar(1024) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(128) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `error_text` varchar(250) DEFAULT NULL,
  `cost_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;