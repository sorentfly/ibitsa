<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Registry
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: String.php 9012 2011-06-22 01:45:16Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Registry
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Registry extends Zend_Registry
{
  /**
   * Class name of the singleton registry object.
   * @var string
   */
  private static $_registryClassName = 'Engine_Registry';

  /**
   * Registry object provides storage for shared objects.
   * @var Zend_Registry
   */
  private static $_registry = null;
  
  /**
   * Service Locator instance
   * 
   * @var Engine_ServiceLocator
   */
  protected $_serviceLocator;
  
  /**
   * B/C mappings
   * 
   * @var array
   */
  protected $_map = array(
    'Engine_Content' => 'content',
    'Engine_Manifest' => 'manifest',
    'Locale' => 'locale',
    'Zend_Cache' => 'cache-default',
    'Zend_Controller_Front' => 'front-default',
    'Zend_Controller_Router' => 'router-default',
    'Zend_Db' => 'database-default',
    'Zend_Layout' => 'layout-default',
    'Zend_Locale' => 'locale',
    'Zend_Translate' => 'translate-default',
  );
  
  /**
   * Retrieves the default registry instance.
   *
   * @return Engine_Registry
   */
  static public function getInstance()
  {
    if( self::$_registry === null ) {
      self::init();
    }

    return self::$_registry;
  }

  /**
   * Set the default registry instance to a specified instance.
   *
   * @param Zend_Registry $registry An object instance of type Zend_Registry,
   *   or a subclass.
   * @return void
   * @throws Zend_Exception if registry is already initialized.
   */
  static public function setInstance(Zend_Registry $registry)
  {
    if( self::$_registry !== null ) {
      // require_once 'Zend/Exception.php';
      throw new Zend_Exception('Registry is already initialized');
    }
    
    parent::setInstance($registry);
    
    self::$_registryClassName = get_class($registry);
    self::$_registry = $registry;
  }

  /**
   * Initialize the default registry instance.
   *
   * @return void
   */
  static protected function init()
  {
    $instance = new self::$_registryClassName();
    
    //parent::setInstance($instance);
    
    self::setInstance($instance);
  }

  /**
   * Set the class name to use for the default registry instance.
   * Does not affect the currently initialized instance, it only applies
   * for the next time you instantiate.
   *
   * @param string $registryClassName
   * @return void
   * @throws Zend_Exception if the registry is initialized or if the
   *   class name is not valid.
   */
  public static function setClassName($registryClassName = 'Engine_Registry')
  {
    if( self::$_registry !== null ) {
      // require_once 'Zend/Exception.php';
      throw new Zend_Exception('Registry is already initialized');
    }

    if( !is_string($registryClassName) ) {
      // require_once 'Zend/Exception.php';
      throw new Zend_Exception("Argument is not a class name");
    }

    /**
     * @see Zend_Loader
     */
    if( !class_exists($registryClassName) ) {
      Engine_Loader::loadClass($registryClassName);
    }
    
    parent::setClassName($registryClassName);

    self::$_registryClassName = $registryClassName;
  }

  /**
   * Unset the default registry instance.
   * Primarily used in tearDown() in unit tests.
   * @returns void
   */
  public static function unsetInstance()
  {
    parent::_unsetInstance();
    self::$_registry = null;
  }

  /**
   * Unset the default registry instance.
   * Primarily used in tearDown() in unit tests.
   * @returns void
   */
  public static function _unsetInstance()
  {
    parent::_unsetInstance();
    self::$_registry = null;
  }

  /**
   * getter method, basically same as offsetGet().
   *
   * This method can be called from an object of type Zend_Registry, or it
   * can be called statically.  In the latter case, it uses the default
   * static instance stored in the class.
   *
   * @param string $index - get the value associated with $index
   * @return mixed
   * @throws Zend_Exception if no entry is registerd for $index.
   */
  public static function get($index)
  {
    $instance = self::getInstance();

    if( !$instance->offsetExists($index) ) {
      // Attempt to load here
      if( null !== $instance->_serviceLocator ) {
        $object = $instance->_serviceLocator->loadForRegistry($index);
      } else {
        $object = null;
      }
      
      if( null === $object ) {
        // require_once 'Zend/Exception.php';
        throw new Zend_Exception("No entry is registered for key '$index'");
      } else {
        $instance->offsetSet($index, $object);
        return $object;
      }
    }

    return $instance->offsetGet($index);
  }

  /**
   * setter method, basically same as offsetSet().
   *
   * This method can be called from an object of type Zend_Registry, or it
   * can be called statically.  In the latter case, it uses the default
   * static instance stored in the class.
   *
   * @param string $index The location in the ArrayObject in which to store
   *   the value.
   * @param mixed $value The object to store in the ArrayObject.
   * @return void
   */
  public static function set($index, $value)
  {
    $instance = self::getInstance();
    $instance->offsetSet($index, $value);
  }

  /**
   * Returns TRUE if the $index is a named value in the registry,
   * or FALSE if $index was not found in the registry.
   *
   * @param  string $index
   * @return boolean
   */
  public static function isRegistered($index)
  {
    if( self::$_registry === null ) {
      return false;
    }
    return self::$_registry->offsetExists($index);
  }
  
  
  
  // Constructor
  
  /**
   * Constructs a parent ArrayObject with default
   * ARRAY_AS_PROPS to allow acces as an object
   *
   * @param array $array data array
   * @param integer $flags ArrayObject flags
   */
  public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
  {
    parent::__construct($array, $flags);
  }
  
  
  
  // Service Locator
  
  public function getServiceLocator()
  {
    return $this->_serviceLocator;
  }
  
  public function setServiceLocator(Engine_ServiceLocator $serviceLocator)
  {
    $this->offsetSet('Engine_ServiceLocator', $serviceLocator);
    $this->_serviceLocator = $serviceLocator;
    return $this;
  }
  
  
  
  // Accessors

  /**
   * @param string $index
   * @returns mixed
   *
   * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
   */
  public function offsetExists($index)
  {
    if( isset($this->_map[$index]) ) {
      $index = $this->_map[$index];
    }
    
    if( array_key_exists($index, $this) ) {
      return true;
    } else if( isset($this->_serviceLocator) && 
        $this->_serviceLocator->hasForRegistry($index) ) {
      return true;
    } else {
      return false;
    }
  }
  
  public function offsetGet($index)
  {
    if( isset($this->_map[$index]) ) {
      $index = $this->_map[$index];
    }
    
    if( array_key_exists($index, $this) ) {
      return parent::offsetGet($index);
    } else if( isset($this->_serviceLocator) && 
        $this->_serviceLocator->hasForRegistry($index) ) {
      $this->offsetSet($index, $this->_serviceLocator->loadForRegistry($index));
      return parent::offsetGet($index);
    } else {
      return null;
    }
  }
  
  public function offsetSet($index, $newval)
  {
    if( isset($this->_map[$index]) ) {
      $index = $this->_map[$index];
    }
    
    return parent::offsetSet($index, $newval);
  }
  
  public function offsetUnset($index)
  {
    if( isset($this->_map[$index]) ) {
      $index = $this->_map[$index];
    }
    
    return parent::offsetUnset($index);
  }
  
  
  
  // Mappings
  
  public function addMapping($from, $to)
  {
    $this->_map[$from] = $to;
    return $this;
  }
  
  public function clearMappings()
  {
    $this->_map = array();
    return $this;
  }
}
