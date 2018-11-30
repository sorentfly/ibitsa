<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Installer extends Engine_Package_Installer_Module
{
  public function onInstall()
  {
    $db = $this->getDb();

    // Run upgrades first to prevent issues with upgrading from older versions
    parent::onInstall();

    
    try {

      // Check for engine4_storage_servicetypes.enabled
      $cols = $db->describeTable('engine4_storage_servicetypes');
      if( empty($cols['enabled']) ) {
        $db->query("
          ALTER TABLE `engine4_storage_servicetypes`
            ADD COLUMN `enabled` tinyint(1) NOT NULL default '1'
        ");

        $db->query("
          UPDATE `engine4_storage_servicetypes`
            SET `enabled` = 0
            WHERE `plugin` IN('Storage_Service_Db', 'Storage_Service_RoundRobin', 'Storage_Service_Mirrored')
        ");
      }

      // Check for engine4_core_menuitems WHERE name=core_admin_main_settings_storage
      $exists = (bool) $db->select()
          ->from('engine4_core_menuitems', new Zend_Db_Expr('TRUE'))
          ->where('`name` = ?', 'core_admin_main_settings_storage')
          ->limit(1)
          ->query()
          ->fetchColumn();
      if( !$exists ) {
        $db->query("
          INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
          ('core_admin_main_settings_storage', 'core', 'Storage System', '', '{\"route\":\"admin_default\",\"module\":\"storage\",\"controller\":\"services\",\"action\":\"index\"}', 'core_admin_main_settings', '', 11)
        ");
      }

    } catch( Exception $e ) {
      $this->_error('Query failed with error: ' . $e->getMessage());
    }
  }
}
