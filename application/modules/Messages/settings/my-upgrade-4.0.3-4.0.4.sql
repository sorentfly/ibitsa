
REPLACE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_message_new', 'messages', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');

ALTER TABLE `engine4_messages_conversations`
ADD COLUMN `title` varchar(255) NOT NULL default ''
AFTER `conversation_id` ;

ALTER TABLE `engine4_messages_conversations`
ADD COLUMN `user_id` int(11) unsigned NOT NULL default 0
AFTER `title` ;

UPDATE `engine4_messages_conversations`
SET `title` = (
  SELECT `title`
  FROM `engine4_messages_messages`
  WHERE `engine4_messages_messages`.`conversation_id` = `engine4_messages_conversations`.`conversation_id`
  ORDER BY `engine4_messages_messages`.`message_id` ASC
  LIMIT 1
) ;

UPDATE `engine4_messages_conversations`
SET `user_id` = (
  SELECT `user_id`
  FROM `engine4_messages_messages`
  WHERE `engine4_messages_messages`.`conversation_id` = `engine4_messages_conversations`.`conversation_id`
  ORDER BY `engine4_messages_messages`.`message_id` ASC
  LIMIT 1
) ;

ALTER TABLE `engine4_messages_conversations`
CHANGE COLUMN `user_id` `user_id` int(11) unsigned NOT NULL ;

DELETE FROM `engine4_core_menuitems` WHERE `name` = 'core_admin_levels_messages' ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_level_messages', 'messages', 'Messages', '', '{"route":"admin_default","module":"messages","controller":"settings","action":"level"}', 'authorization_admin_level', '', 3)
;