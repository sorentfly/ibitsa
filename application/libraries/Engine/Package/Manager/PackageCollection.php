<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PackageCollection.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager_PackageCollection extends Engine_Cache_ArrayContainer
{
  protected $_manager;

  protected $_indexByGuid;

  public function __construct(Engine_Package_Manager $manager, array $packages = array(), array $options = array())
  {
    // Set manager
    $this->_manager = $manager;

    // Prepare packages
    $data = array();
    $indexByGuid = array();
    foreach( $packages as $package ) {
      if( !$package instanceof Engine_Package_Manifest ) {
        throw new Engine_Package_Manager_Exception('Not a package');
      }
      $guid = $package->getGuid();
      $key = $package->getKey();
      $data[$key] = $package;
      if( !isset($indexByGuid[$key]) ) {
        $indexByGuid[$guid] = $key;
      } else {
        throw new Engine_Package_Manager_Exception('Does not support multiple versions of the same package');
      }
    }
    unset($package);
    unset($packages);
    $this->_indexByGuid = $indexByGuid;

    if( !isset($options['persistent']) ) {
      $options['persistent'] = false;
    }

    parent::__construct($data, $this->_manager->getCache(), $options);
  }

  public function getManager()
  {
    if( null === $this->_manager ) {
      throw new Engine_Package_Manager_Exception('Manager not set in package list');
    }
    return $this->_manager;
  }

  public function setManager(Engine_Package_Manager $manager)
  {
    //if( null !== $this->_manager ) {
    //  throw new Engine_Package_Manager_Exception('Manager already defined');
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



  // Magic

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_indexByGuid',
    ));
  }

  public function __wakeup()
  {

  }

  /**
   * @param string $key
   * @return Engine_Package_Manifest
   */
  public function __get($key)
  {
    if( !$this->__isset($key) ) {
      if( isset($this->_indexByGuid[$key]) ) {
        $key = $this->_indexByGuid[$key];
      } else {
        return null;
      }
      //$a = 1;
    }

    $value = parent::__get($key);
    //if( $a ) {
    //  var_dump($value);die();
    //}

    // Only packages
    if( !($value instanceof Engine_Package_Manifest) ) {
      //var_dump($key);
      //var_dump($this->_data);
      //var_dump($value);
      //var_dump(scandir(APPLICATION_PATH . '/temporary/cache'));
      //die();
      throw new Engine_Package_Manager_Exception('Problem loading package from cache');
    }
    
    return $value;
  }

  /**
   * @return Engine_Package_Manifest
   */
  public function current()
  {
    return parent::current();
  }

  /**
   * @return Engine_Package_Manifest
   */
  public function offsetGet($offset)
  {
    return parent::offsetGet($offset);
  }

  public function __set($key, $value)
  {
    // Only packages
    if( !($value instanceof Engine_Package_Manifest) ) {
      throw new Engine_Package_Manager_Exception('Cannot assign a non-package');
    }

    // Ignore the key
    $guid = $value->getGuid();
    $key = $value->getKey();
    if( !$this->__isset($key) && isset($this->_indexByGuid[$guid]) ) {
      //$tmpValue = $this->__get($key);
      //if( $tmpValue->getKey() != $value->getKey() ) {
      if( $key != $this->_indexByGuid[$guid] ) {
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
    // Only packages
    if( !($value instanceof Engine_Package_Manifest) ) {
      throw new Engine_Package_Manager_Exception('Cannot assign a non-package');
    }
    $key = $value->getKey();
    return parent::append($value, $key);
  }

  public function prepend($value, $key = null)
  {
    // Only packages
    if( !($value instanceof Engine_Package_Manifest) ) {
      throw new Engine_Package_Manager_Exception('Cannot assign a non-package');
    }
    $key = $value->getKey();
    return parent::prepend($value, $key);
  }
}