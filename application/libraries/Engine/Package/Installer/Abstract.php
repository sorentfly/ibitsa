<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Package_Installer_Abstract
{
  /**
   * The active operation
   * 
   * @var Engine_Package_Manager_Operation_Abstract
   */
  protected $_operation;

  /**
   * Database adapter
   * 
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_db;

  /**
   * Virtual File System Adapter
   * 
   * @var Engine_Vfs_Adapter_Abstract
   */
  protected $_vfs;

  /**
   * The module name
   * 
   * @var string
   */
  protected $_name;

  protected $_classMethods;

  protected $_errors = array();

  protected $_messages = array();

  /**
   * Constructor
   * 
   * @param Engine_Package_Manager_Operation_Abstract $operation
   * @param Zend_Db_Adapter_Abstract $db
   * @param array $options
   */
  final public function __construct(Engine_Package_Manager_Operation_Abstract $operation, array $options = null)
  {
    $this->_operation = $operation;
    $this->_name = $operation->getPrimaryPackage()->getName();

    if( is_array($options) ) {
      $this->setOptions($options);
    }
    
    $this->init();
  }

  /**
   * Set options
   * 
   * @param array $options
   * @return self
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

  public function setOperation(Engine_Package_Manager_Operation_Abstract $operation)
  {
    if( null !== $this->_operation ) {
      throw new Engine_Package_Installer_Exception('Operation already set');
    }
    $this->_operation = $operation;
    return $this;
  }

  /**
   * @return Engine_Package_Manager_Operation_Abstract
   */
  public function getOperation()
  {
    if( null === $this->_operation ) {
      throw new Engine_Package_Installer_Exception('Operation not set');
    }
    return $this->_operation;
  }

  public function setDb(Zend_Db_Adapter_Abstract $db = null)
  {
    if( null !== $this->_db ) {
      throw new Engine_Package_Installer_Exception('Database already set');
    }
    $this->_db = $db;
    return $this;
  }

  /**
   * @return Zend_Db_Adapter_Abstract
   */
  public function getDb()
  {
    if( null === $this->_db ) {
      throw new Engine_Package_Installer_Exception('Database not set');
    }
    return $this->_db;
  }

  public function setVfs(Engine_Vfs_Adapter_Abstract $vfs = null)
  {
    if( null !== $this->_vfs ) {
      throw new Engine_Package_Installer_Exception('VFS already set');
    }
    $this->_vfs = $vfs;
    return $this;
  }

  /**
   * @return Engine_Vfs_Adapter_Abstract
   */
  public function getVfs()
  {
    if( null === $this->_vfs ) {
      throw new Engine_Package_Installer_Exception('VFS not set');
    }
    return $this->_vfs;
  }



  // Events
  
  public function notify($type)
  {
    foreach( $this->_getClassMethods() as $method ) {
      if( substr($method, 0, 2) != 'on' ) continue;
      if( strtolower($method) === strtolower('on' . $type) ) {
        try {
          $ret = $this->$method();
        } catch( Exception $e ) {
          $this->_error('Error: ' . $e->getMessage());
          return false;
        }
        return true;
      }
    }

    return null;
  }

  /**
   * Internal construct hook
   *
   * @return void
   */
  public function init()
  {

  }


  // Messages/Errors

  protected function _error($error)
  {
    $this->_errors[] = $error;
    return $this;
  }

  protected function _message($message)
  {
    $this->_messages[] = $message;
    return $this;
  }

  public function hasError()
  {
    return !empty($this->_errors);
  }

  public function hasMessages()
  {
    return !empty($this->_messages);
  }

  public function getErrors()
  {
    return $this->_errors;
  }

  public function getMessages()
  {
    return $this->_messages;
  }

  public function clearErrors()
  {
    $this->_errors = array();
    return $this;
  }

  public function clearMessages()
  {
    $this->_messages = array();
    return $this;
  }



  // Utilities

  /**
   * Get the current versions. Key 0 should be the target version, Key 1 should
   * be the currently installed version
   *
   * @return array
   */
  protected function _getVersions()
  {
    $currentVersionPackage = $this->_getVersionPackage();
    //$currentVersionManifest = $this->_getVersionManifest();
    $currentVersionDatabase = $this->_getVersionDatabase();
    $targetVersion = $this->_getVersionTarget();
    
    $currentVersion = ( $currentVersionDatabase ? $currentVersionDatabase : $currentVersionPackage );

    return array(
      $targetVersion,
      $currentVersion
    );
  }

  /**
   * Get the target version
   * 
   * @return string
   */
  protected function _getVersionTarget()
  {
    if( $this->getOperation()->getOperationType() == 'remove' ) {
      return null;
    } else {
      return $this->getOperation()->getPrimaryPackage()->getVersion();
    }
  }

  /**
   * Get the current version as defined by the package file
   * 
   * @return string
   */
  protected function _getVersionPackage()
  {
    $previousPackage = $this->getOperation()->getPreviousPackage();
    if( null !== $previousPackage ) {
      return $previousPackage->getVersion();
    }
    return null;
  }

  /**
   * Get the current version as defined by the manifest file
   * 
   * @return string
   */
  protected function _getVersionManifest()
  {
    $package = $this->getOperation()->getPrimaryPackage();
    $path = $package->getPath();

    if( $this instanceof Engine_Package_Installer_Module ) {
      $manifestPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'manifest.php';
    } else {
      $manifestPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'manifest.php';
    }

    if( !file_exists($manifestPath) ) {
      return null;
    }

    ob_start();
    $config = include $manifestPath;
    ob_end_clean();

    if( isset($config['package']['version']) ) {
      return $config['package']['version'];
    }

    return null; // hmmmm...
  }

  /**
   * Get the current version as defined by the database
   * 
   * @return string
   */
  protected function _getVersionDatabase()
  {
    return null;
  }

  protected function _getClassMethods()
  {
    if( null === $this->_classMethods ) {
      $this->_classMethods = array_map('strtolower', get_class_methods(get_class($this)));
    }
    return $this->_classMethods;
  }
}