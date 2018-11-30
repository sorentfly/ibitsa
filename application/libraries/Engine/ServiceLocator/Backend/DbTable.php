<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: DbTable.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_ServiceLocator_Backend_DbTable extends Engine_ServiceLocator_Backend_Abstract
{
  // Properties
  
  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_dbAdapter;
  
  /**
   *
   * @var Zend_Db_Table_Abstract|string
   */
  protected $_dbTable;
  
  
  
  // General
  
  /**
   * Constructor
   * 
   * @param array $options 
   */
  public function __construct(array $options = null)
  {
    if( null !== $options ) {
      $this->setOptions($options);
    }

    // Initialize
    $this->getDbAdapter();
    $this->getDbTable();

    $this->_load();
  }
  
  /**
   * Set options
   * 
   * @param array $options
   * @return Engine_ServiceLocator_Backend_DbTable 
   */
  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }
  
  /**
   * Get configured database adapter
   * 
   * @return Zend_Db_Adapter_Abstract
   */
  public function getDbAdapter()
  {
    if( null === $this->_dbAdapter ) {
      if( $this->_dbTable instanceof Zend_Db_Table_Abstract ) {
        $this->_dbAdapter = $this->_dbTable->getAdapter();
      } else {
        throw new Engine_ServiceLocator_Exception('Database adapter not configured');
      }
    }

    return $this->_dbAdapter;
  }
  
  /**
   * Set database adapter
   * 
   * @param Zend_Db_Adapter_Abstract $dbAdapter
   * @return Engine_ServiceLocator_Backend_DbTable 
   */
  public function setDbAdapter($dbAdapter)
  {
    if( !($dbAdapter instanceof Zend_Db_Adapter_Abstract) ) {
      throw new Engine_ServiceLocator_Exception('Invalid database adapter');
    }

    $this->_dbAdapter = $dbAdapter;

    return $this;
  }
  
  /**
   * Get configured database table
   * 
   * @return Zend_Db_Table_Abstract|string
   */
  public function getDbTable()
  {
    if( null === $this->_dbTable ) {
      throw new Engine_ServiceLocator_Exception('Invalid database table');
    } else if( is_string($this->_dbTable) ) {
      return $this->_dbTable;
    } else if( $this->_dbTable instanceof Zend_Db_Table_Abstract ) {
      return $this->_dbTable->info('name');
    } else {
      throw new Engine_ServiceLocator_Exception('Invalid database table');
    }
  }
  
  /**
   * Set database table
   * 
   * @param Zend_Db_Table_Abstract $dbTable
   * @return Engine_ServiceLocator_Backend_DbTable 
   */
  public function setDbTable($dbTable)
  {
    if( is_string($dbTable) ) {
      $this->_dbTable = $dbTable;
    } else if( $dbTable instanceof Zend_Db_Table_Abstract ) {
      $this->_dbTable = $dbTable;
      $this->_dbAdapter = $dbTable->getAdapter();
    } else {
      throw new Engine_ServiceLocator_Exception('Invalid database table');
    }

    return $this;
  }
  
  
  
  // Accessors
  
  /**
   * Get the resource configuration
   * 
   * @param string $type
   * @param string $profile
   * @return mixed
   */
  public function get($type, $profile = null)
  {
    //if( is_numeric($type) ) {
    //  $key = 'service-' . $type;
    //} else {
      $key = $type . '-' . ( $profile ? $profile : 'default' );
    //}
    
    if( isset($this->_data[$key]) &&
        is_array($this->_data[$key]) ) {
      return $this->_data[$key];
    } else {
      return null;
    }
  }
  
  /**
   * Check if the resource configuration exists
   * 
   * @param string $type
   * @param string $profile
   * @return boolean 
   */
  public function has($type, $profile = null)
  {
    //if( is_numeric($type) ) {
    //  $key = 'service-' . $type;
    //} else {
      $key = $type . '-' . ( $profile ? $profile : 'default' );
    //}
    
    return ( isset($this->_data[$key]) && is_array($this->_data[$key]) );
  }
  
  /**
   * Set the resource configuration
   * 
   * @param string $type
   * @param array $value
   * @param string $profile
   * @return Engine_ServiceLocator_Backend_DbTable 
   */
  public function set($type, $value, $profile = null)
  {
    //if( is_numeric($type) ) {
    //  $key = 'service-' . $type;
    //} else {
      $key = $type . '-' . ( $profile ? $profile : 'default' );
    //}
    
    $this->_data[$key] = $value;
    return $this;
  }
  
  
  
  // Utility
  
  /**
   * Load the resource configuration
   * 
   * @return void
   */
  protected function _load()
  {
    $table = $this->getDbTable();
    $db = $this->getDbAdapter();
    if( is_string($table) ) {
      $rawData = $db->select()
          ->from($table)
          ->where('enabled = ?', true)
          ->query()
          ->fetchAll();
    } else if( $table instanceof Zend_Db_Table_Abstract ) {
      $rawData = $db->select()
          ->from($table)
          ->where('enabled = ?', true)
          ->query()
          ->fetchAll();
    } else {
      throw new Engine_ServiceLocator_Exception('Invalid database table');
    }

    $this->_data = array();
    foreach( $rawData as $rawDatum ) {
      if( is_array($rawDatum['config']) ) {
        $config = $rawDatum['config'];
      } else if( ($tmp = Zend_Json::decode($rawDatum['config'])) &&
          is_array($tmp) ) {
        $config = $tmp;
      } else if( ($tmp = unserialize($rawDatum['config'])) &&
          is_array($tmp) ) {
        $config = $tmp;
      } else {
        continue; // throw?
      }
      
      $key = $rawDatum['type'] . '-' 
          . ( !empty($rawDatum['profile']) ? $rawDatum['profile'] : 'default' );
      
      //$this->_data['service-' . $rawData['service_id']] = $config;
      $this->_data[$key] = $config;
    }
  }
}
