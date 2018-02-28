<?php
@defined('APPROOT') or header('Page Not Found', true, 404) || die();

$tables ['1.0.0'] [] = "CREATE TABLE IF NOT EXISTS `{prefix}sms_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `create_time` INT UNSIGNED NOT NULL COMMENT '发送时间',
    `tid` VARCHAR(64) NOT NULL COMMENT '业务ID',
	`phone` VARCHAR(11) NOT NULL COMMENT '手机号码',
    `vendor` VARCHAR(16) NOT NULL COMMENT '提供商',
    `status` TINYINT UNSIGNED NOT NULL COMMENT '状态,1:成功，0：失败',
    `content` VARCHAR(256) NOT NULL COMMENT '内容',
    `note` VARCHAR(512) NULL COMMENT '发送失败时错误信息',
    PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='短信发送日志'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}sms_vendor` (
    `id` VARCHAR(16) NOT NULL COMMENT '供应商ID',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否启用',
    `options` TEXT NULL COMMENT '配置',
    PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='短信提供商'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}sms_tpl` (
    `id` VARCHAR(16) NOT NULL COMMENT '供应商ID',
    `tpl` VARCHAR(32) NOT NULL COMMENT '模板编号 ',
    `content` VARCHAR(256) NULL COMMENT '自定义模板',
    `interval` SMALLINT NOT NULL DEFAULT 120 COMMENT '发送间隔',
    PRIMARY KEY (`id` , `tpl`)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='短信模板配置表'";