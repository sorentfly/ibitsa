
ALTER TABLE `engine4_messages_conversations`
  ADD COLUMN `resource_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci default '',
  ADD COLUMN `resource_id` int(11) unsigned NOT NULL default '0' ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('messages_main_inbox', 'messages', 'Inbox', '', '{"route":"messages_general","action":"inbox"}', 'messages_main', '', 1),
('messages_main_outbox', 'messages', 'Sent Messages', '', '{"route":"messages_general","action":"outbox"}', 'messages_main', '', 2),
('messages_main_compose', 'messages', 'Compose Message', '', '{"route":"messages_general","action":"compose"}', 'messages_main', '', 3)
;

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('messages_main', 'standard', 'Messages Main Navigation Menu')
;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'messages' as `type`,
    'auth' as `name`,
    3 as `value`,
    'friends' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
