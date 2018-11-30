<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Transaction.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager_Transaction extends Engine_Cache_ArrayContainer
{
  protected $_manager;

  protected $_indexByGuid;

  protected $_dependencies;
  
  public function __construct(Engine_Package_Manager $manager, array $operations = array(), $options = array())
  {
    // Set manager
    $this->_manager = $manager;

    // Prepare operations
    $data = array();
    $indexByGuid = array();
    foreach( $operations as $operation ) {
      if( !$operation instanceof Engine_Package_Manager_Operation_Abstract ) {
        throw new Engine_Package_Manager_Exception('Not an operation');
      }
      $operation->setManager($manager);
      $guid = $operation->getGuid();
      $key = $operation->getKey();
      $data[$key] = $operation;
      if( !isset($indexByGuid[$key]) ) {
        $indexByGuid[$guid] = $key;
      } else {
        throw new Engine_Package_Manager_Exception('Does not support multiple versions of the same package');
      }
    }
    unset($operation);
    unset($operations);
    $this->_indexByGuid = $indexByGuid;
   
    parent::__construct($data, $this->_manager->getCache(), $options);
  }

  public function getManager()
  {
    if( null === $this->_manager ) {
      throw new Engine_Package_Manager_Exception('Manager not set in transaction');
    }
    return $this->_manager;
  }

  public function setManager(Engine_Package_Manager $manager)
  {
    //if( null !== $this->_manager ) {
    //  throw new Engine_Package_Manager_Operation_Exception('Manager already defined');
    //}
    $this->_manager = $manager;
    return $this;
  }

  public function hasGuid($guid)
  {
    return isset($this->_indexByGuid[$guid]);
  }

  public function getKeyByGuid($guid)
  {
    if( isset($this->_indexByGuid[$guid]) ) {
      return $this->_indexByGuid[$guid];
    }
    return null;
  }

  public function getIndexByGuid()
  {
    return $this->_indexByGuid;
  }



  // Dependencies
  
  public function setDependencies(Engine_Package_Manager_Dependencies $dependencies)
  {
    $this->_dependencies = $dependencies;
    return $this;
  }

  public function getDependencies()
  {
    if( null === $this->_dependencies ) {
      $this->_dependencies = $this->_manager->depend($this);
    }
    return $this->_dependencies;
  }



  // Tests

  public function getTests()
  {
    return $this->_manager->test($this);
  }



  // Diff
  
  public function getFileOperations($skipErrors = false, $showAll = false)
  {
    $fileOperations = array();
    foreach( $this->getArrayKeys() as $key ) {
      $operation = $this->__get($key);
      $fileOperations[$key] = $operation->getFileOperations($skipErrors, $showAll);
      unset($operation);
    }
    return $fileOperations;
  }



  // Magic

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_indexByGuid', '_dependencies'
    ));
  }

  public function __wakeup()
  {
    
  }

  /**
   * @param string $key
   * @return Engine_Package_Manager_Operation_Abstract
   */
  public function __get($key)
  {
    $oldKey = $key;
    if( !$this->__isset($key) && isset($this->_indexByGuid[$key]) ) {
      $key = $this->_indexByGuid[$key];
    }

    $value = parent::__get($key);

    // Only operations
    if( !($value instanceof Engine_Package_Manager_Operation_Abstract) ) {
      throw new Engine_Package_Manager_Exception('Problem loading operation from cache');
    }

    $value->setManager($this->_manager);

    return $value;
  }

  /**
   * @return Engine_Package_Manager_Operation_Abstract
   */
  public function current()
  {
    return parent::current();
  }

  /**
   * @return Engine_Package_Manager_Operation_Abstract
   */
  public function offsetGet($offset)
  {
    return parent::offsetGet($offset);
  }

  public function __set($key, $value)
  {
    // Only operations
    if( !($value instanceof Engine_Package_Manager_Operation_Abstract) ) {
      throw new Engine_Package_Manager_Exception('Problem loading operation from cache');
    }

    // Ignore the key
    $guid = $value->getGuid();
    $key = $value->getKey();
    if( !$this->__isset($key) && isset($this->_indexByGuid[$guid]) ) {
      $tmpValue = $this->__get($key);
      if( $tmpValue->getKey() != $value->getKey() ) {
        throw new Engine_Package_Manager_Exception('Does not support multiple versions of the same package');
      }
    }
    
    $this->_indexByGuid[$guid] = $key;

    parent::__set($key, $value);
  }

  public function __unset($key)
  {
    $guid = null;
    if( !$this->__isset($key) ) {
      if( isset($this->_indexByGuid[$key]) ) {
        $guid = $key;
        $key = $this->_indexByGuid[$key];
      } else {
        return;
      }
    } else {
      foreach( $this->_indexByGuid as $tmpGuid => $tmpKey ) {
        if( $key == $tmpKey ) {
          $guid = $tmpGuid;
        }
      }
    }

    if( $guid ) {
      unset($this->_indexByGuid[$guid]);
    }
    
    parent::__unset($key);
  }
  
  public function append($value, $key = null)
  {
    // Only operations
    if( !($value instanceof Engine_Package_Manager_Operation_Abstract) ) {
      throw new Engine_Package_Manager_Exception('Problem loading operation from cache');
    }
    $key = $value->getKey();
    return parent::append($value, $key);
  }

  public function prepend($value, $key = null)
  {
    // Only operations
    if( !($value instanceof Engine_Package_Manager_Operation_Abstract) ) {
      throw new Engine_Package_Manager_Exception('Problem loading operation from cache');
    }
    $key = $value->getKey();
    return parent::prepend($value, $key);
  }
}