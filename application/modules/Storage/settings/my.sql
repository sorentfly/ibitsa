
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: my.sql 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_storage_chunks`
--

DROP TABLE IF EXISTS `engine4_storage_chunks`;
CREATE TABLE IF NOT EXISTS `engine4_storage_chunks` (
  `chunk_id` bigint(20) unsigned NOT NULL auto_increment,
  `file_id` int(11) unsigned NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY  (`chunk_id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_storage_files`
--

DROP TABLE IF EXISTS `engine4_storage_files`;
CREATE TABLE `engine4_storage_files` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `parent_file_id` int(10) unsigned NULL,
  `type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,

  `parent_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `parent_id` int(10) unsigned default NULL,
  `user_id` int(10) unsigned default NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,

  `service_id` int(10) unsigned NOT NULL default '1',
  `storage_path` varchar(255) NOT NULL,
  `extension` varchar(8) NOT NULL,
  `name` varchar(255) default NULL,
  `mime_major` varchar(64) NOT NULL,
  `mime_minor` varchar(64) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `hash` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,

  PRIMARY KEY  (`file_id`),
  UNIQUE KEY  (`parent_file_id`,`type`),
  KEY `PARENT` (`parent_type`,`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_storage_mirrors`
--

DROP TABLE IF EXISTS `engine4_storage_mirrors`;
CREATE TABLE IF NOT EXISTS `engine4_storage_mirrors` (
  `file_id` bigint(20) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`file_id`,`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_storage_services`
--

DROP TABLE IF EXISTS `engine4_storage_services`;
CREATE TABLE IF NOT EXISTS `engine4_storage_services` (
  `service_id` int(10) unsigned NOT NULL auto_increment,
  `servicetype_id` int(10) unsigned NOT NULL,
  `config` text CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `default` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_storage_services`
--

INSERT IGNORE INTO `engine4_storage_services` (`service_id`, `servicetype_id`, `enabled`, `default`) VALUES
(1, 1, 1, 1);


-- --------------------------------------------------------

--
-- Table structure for table `engine4_storage_servicetypes`
--

DROP TABLE IF EXISTS `engine4_storage_servicetypes`;
CREATE TABLE IF NOT EXISTS `engine4_storage_servicetypes` (
  `servicetype_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `form` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`servicetype_id`),
  UNIQUE KEY `plugin` (`plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

--
-- Dumping data for table `engine4_storage_servicetypes`
--

/* 1 must be the ID of the local storage service because of the default column value in the files table */
INSERT INTO `engine4_storage_servicetypes` (`servicetype_id`, `title`, `plugin`, `form`) VALUES
(1, 'Local Storage', 'Storage_Service_Local', 'Storage_Form_Admin_Service_Local');

INSERT INTO `engine4_storage_servicetypes` (`title`, `plugin`, `form`, `enabled`) VALUES
('Database Storage', 'Storage_Service_Db', 'Storage_Form_Admin_Service_Db', 0),
('Amazon S3', 'Storage_Service_S3', 'Storage_Form_Admin_Service_S3', 1),
('Virtual File System', 'Storage_Service_Vfs', 'Storage_Form_Admin_Service_Vfs', 1),
('Round-Robin', 'Storage_Service_RoundRobin', 'Storage_Form_Admin_Service_RoundRobin', 0),
('Mirrored', 'Storage_Service_Mirrored', 'Storage_Form_Admin_Service_Mirrored', 0);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `form`) VALUES
('Storage Transfer', 'storage_transfer', 'core', 'Storage_Plugin_Job_Transfer', 'Core_Form_Admin_Job_Generic'),
('Storage Cleanup', 'storage_cleanup', 'core', 'Storage_Plugin_Job_Cleanup', 'Core_Form_Admin_Job_Generic');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_storage', 'core', 'Storage System', '', '{"route":"admin_default","module":"storage","controller":"services","action":"index"}', 'core_admin_main_settings', '', 11);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('storage', 'Storage', 'Storage', '4.2.3', 1, 'core');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('storage.service.mirrored.counter', '0'),
('storage.service.mirrored.index', '0'),
('storage.service.roundrobin.counter', '0');
