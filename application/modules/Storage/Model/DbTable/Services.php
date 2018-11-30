<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Services.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Model_DbTable_Services extends Engine_Db_Table
{
  protected $_defaultServiceIdentity;

  protected $_services = array();

  public function getDefaultServiceIdentity()
  {
    if( null === $this->_defaultServiceIdentity ) {
      $this->_defaultServiceIdentity = $this->select()
        ->from($this, 'service_id')
        ->where('`default` = ?', 1)
        ->limit(1)
        ->query()
        ->fetchColumn();
      if( !$this->_defaultServiceIdentity ) {
        throw new Storage_Model_Exception('Unable to find default storage service');
      }
    }
    return $this->_defaultServiceIdentity;
  }
  
  public function getService($serviceIdentity = null)
  {
    if( null === $serviceIdentity ) {
      $serviceIdentity = $this->getDefaultServiceIdentity();
    } else if( !is_numeric($serviceIdentity) ) {
      throw new Storage_Model_Exception('Invalid storage service identity specifier');
    }
    
    if( !isset($this->_services[$serviceIdentity]) ) {
      // Get service info
      $serviceInfo = $this->select()
        ->where('service_id = ?', $serviceIdentity)
        ->where('enabled = ?', 1)
        ->limit(1)
        ->query()
        ->fetch();
      if( !$serviceInfo ) {
        throw new Storage_Model_Exception(sprintf('Missing storage service "%s"', $serviceIdentity));
      }

      // Get service type info
      $serviceTypeIdentity = $serviceInfo['servicetype_id'];
      $serviceTypeInfo = Engine_Api::_()->getDbtable('serviceTypes', 'storage')->select()
        ->where('servicetype_id = ?', $serviceTypeIdentity)
        ->limit(1)
        ->query()
        ->fetch();
      if( !$serviceTypeInfo ) {
        throw new Storage_Model_Exception(sprintf('Missing storage service type "%s"', $serviceTypeIdentity));
      }

      $class = $serviceTypeInfo['plugin'];
      Engine_Loader::loadClass($class);

      if( !class_exists($class, false) ||
          !in_array('Storage_Service_Interface', class_implements($class)) ) {
        throw new Storage_Model_Exception(sprintf('Missing storage service ' .
            'class or does not implement Storage_Service_Interface for ' .
            'service "%s"', $serviceIdentity));
      }
      
      $config = array();
      if( !empty($serviceInfo['config']) ) {
        $config = Zend_Json::decode($serviceInfo['config']);
        if( !is_array($config) ) {
          $config = array();
        }
      }
      $config['service_id'] = $serviceInfo['service_id'];

      $this->_services[$serviceIdentity] = new $class($config);
    }
    
    return $this->_services[$serviceIdentity];
  }
}