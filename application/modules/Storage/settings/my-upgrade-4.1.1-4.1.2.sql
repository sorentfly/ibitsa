
ALTER TABLE `engine4_storage_servicetypes` ADD UNIQUE KEY `plugin` (`plugin`);

/* 1 must be the ID of the local storage service because of the default column value in the files table */
INSERT IGNORE INTO `engine4_storage_servicetypes` (`servicetype_id`, `title`, `plugin`, `form`) VALUES
(1, 'Local Storage', 'Storage_Service_Local', 'Storage_Form_Admin_Service_Local');

INSERT IGNORE INTO `engine4_storage_servicetypes` (`title`, `plugin`, `form`) VALUES
('Database Storage', 'Storage_Service_Db', 'Storage_Form_Admin_Service_Db'),
('Amazon S3', 'Storage_Service_S3', 'Storage_Form_Admin_Service_S3'),
('Virtual File System', 'Storage_Service_Vfs', 'Storage_Form_Admin_Service_Vfs'),
('Round-Robin', 'Storage_Service_RoundRobin', 'Storage_Form_Admin_Service_RoundRobin'),
('Mirrored', 'Storage_Service_Mirrored', 'Storage_Form_Admin_Service_Mirrored');

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('storage.service.mirrored.counter', '0'),
('storage.service.mirrored.index', '0'),
('storage.service.roundrobin.counter', '0');

DROP TABLE IF EXISTS `engine4_storage_mirrors`;
CREATE TABLE IF NOT EXISTS `engine4_storage_mirrors` (
  `file_id` bigint(20) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`file_id`,`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

ALTER TABLE `engine4_storage_files` ADD KEY `service_id` (`service_id`);

ALTER TABLE `engine4_storage_files`
  CHANGE `parent_type` `parent_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci default NULL ;

/* Fix some incorrect default collations */
ALTER TABLE `engine4_storage_chunks` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
