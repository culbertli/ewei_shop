<?php 
pdo_query("
SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `ims_ewei_shop_goods` MODIFY COLUMN `membercardpoint`  int(2) NOT NULL DEFAULT 0 AFTER `wd_new`;
ALTER TABLE `ims_ewei_shop_member` ADD COLUMN `456wd_id`  int(11) NOT NULL AFTER `applyagenttime`;
ALTER TABLE `ims_ewei_shop_member` ADD COLUMN `wd_new`  int(11) NOT NULL AFTER `456wd_id`;
ALTER TABLE `ims_ewei_shop_member` ADD COLUMN `is_reset`  tinyint(1) NOT NULL DEFAULT 0 AFTER `wd_new`;
ALTER TABLE `ims_ewei_shop_member` ADD COLUMN `old_openid`  varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `is_reset`;
ALTER TABLE `ims_ewei_shop_member` ADD COLUMN `jpush`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `old_openid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diymaxcredit`  tinyint(3) NULL DEFAULT 0 AFTER `credit2`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `maxcredit`  int(11) NULL DEFAULT 0 AFTER `diymaxcredit`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `agentnotupgrade`  int(11) NULL DEFAULT 0 AFTER `childtime`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `username`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `agentblack`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diymemberdataid`  int(11) NULL DEFAULT 0 AFTER `diymemberid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diymemberdata`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `diymemberdataid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diycommissionid`  int(11) NULL DEFAULT 0 AFTER `diymemberdata`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diycommissiondataid`  int(11) NULL DEFAULT 0 AFTER `diycommissionid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diycommissiondata`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `diycommissiondataid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `isblack`  int(11) NULL DEFAULT 0 AFTER `diycommissiondata`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diyaagentid`  int(11) NULL DEFAULT 0 AFTER `aagentnotupgrade`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diyaagentdata`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `diyaagentid`;
ALTER TABLE `ims_ewei_shop_member` MODIFY COLUMN `diyaagentfields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `diyaagentdata`;
ALTER TABLE `ims_ewei_shop_order_peerpay_payinfo` ADD COLUMN `paytype`  tinyint(1) NOT NULL DEFAULT 0 AFTER `openid`;
ALTER TABLE `ims_ewei_shop_order_peerpay_payinfo` MODIFY COLUMN `tid`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `refundprice`;
DROP INDEX `channel` ON `ims_ewei_shop_queue`;
CREATE INDEX `channel` ON `ims_ewei_shop_queue`(`channel`) USING BTREE ;
SET FOREIGN_KEY_CHECKS=1;

");