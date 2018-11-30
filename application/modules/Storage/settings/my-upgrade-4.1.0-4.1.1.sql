
INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `form`) VALUES
('Storage Transfer', 'storage_transfer', 'core', 'Storage_Plugin_Job_Transfer', 'Core_Form_Admin_Job_Generic'),
('Storage Cleanup', 'storage_cleanup', 'core', 'Storage_Plugin_Job_Cleanup', 'Core_Form_Admin_Job_Generic');

ALTER TABLE `engine4_storage_files`
  ADD COLUMN `service_id` int(11) unsigned NOT NULL default '1'
  AFTER `modified_date` ;

ALTER TABLE `engine4_storage_files`
  DROP COLUMN `storage_type` ;


/* storage services */
DROP TABLE IF EXISTS `engine4_storage_services`;
CREATE TABLE IF NOT EXISTS `engine4_storage_services` (
  `service_id` int(10) unsigned NOT NULL auto_increment,
  `servicetype_id` int(10) unsigned NOT NULL,
  `config` text CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `default` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

INSERT IGNORE INTO `engine4_storage_services` (`service_id`, `servicetype_id`, `enabled`, `default`) VALUES
(1, 1, 1, 1);

/* storage servicetypes */
DROP TABLE IF EXISTS `engine4_storage_servicetypes`;
CREATE TABLE IF NOT EXISTS `engine4_storage_servicetypes` (
  `servicetype_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `form` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  PRIMARY KEY  (`servicetype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

/* 1 must be the ID of the local storage service because of the default column value in the files table */
INSERT IGNORE INTO `engine4_storage_servicetypes` (`servicetype_id`, `title`, `plugin`, `form`) VALUES
(1, 'Local Storage', 'Storage_Service_Local', 'Storage_Form_Admin_Service_Local');

INSERT IGNORE INTO `engine4_storage_servicetypes` (`title`, `plugin`, `form`) VALUES
('Database Storage', 'Storage_Service_Db', 'Storage_Form_Admin_Service_Db'),
('Amazon S3', 'Storage_Service_S3', 'Storage_Form_Admin_Service_S3'),
('Virtual File System', 'Storage_Service_Vfs', 'Storage_Form_Admin_Service_Vfs'),
('Round-Robin', 'Storage_Service_RoundRobin', 'Storage_Form_Admin_Service_RoundRobin'),
('Mirrored', 'Storage_Service_Mirrored', 'Storage_Form_Admin_Service_Mirrored');
