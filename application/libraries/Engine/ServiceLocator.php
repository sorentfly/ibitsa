<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: String.php 9012 2011-06-22 01:45:16Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_ServiceLocator
{
  // Properties
  
  /**
   * @var Engine_ServiceLocator_Backend_Abstract
   */
  protected $_backend;
  
  
  
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
    
    // Make sure we have a backend configured
    $this->getBackend();
  }
  
  /**
   * Set options
   * 
   * @param array $options
   * @return Engine_ServiceLocator 
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
   * Get the configured service locator backend
   * 
   * @return Engine_ServiceLocator_Backend_Abstract
   */
  public function getBackend()
  {
    if( null === $this->_backend ) {
      throw new Engine_ServiceLocator_Exception('No backend configured');
    }
    
    return $this->_backend;
  }
  
  /**
   * Set the configured service locator backend
   *
   * @param Engine_ServiceLocator_Backend_Abstract|array $backend
   * @return Engine_ServiceLocator 
   */
  public function setBackend($backend)
  {
    if( $backend instanceof Engine_ServiceLocator_Backend_Abstract ) {
      $this->_backend = $backend;
    } else if( is_array($backend) ) {
      if( isset($backend['class']) ) {
        $class = $backend['class'];
      } else if( isset($backend['type']) ) {
        $class = 'Engine_ServiceLocator_Backend_' . ucfirst($backend['type']);
      } else {
        throw new Engine_ServiceLocator_Exception('No backend specified');
      }
      if( isset($backend['options']) && is_array($backend['options']) ) {
        $options = $backend['options'];
      } else {
        $options = $backend;
        unset($backend['class']);
        unset($backend['type']);
      }
      
      $this->_backend = new $class($options);
    } else {
      throw new Engine_ServiceLocator_Exception('Unknown backend specifier');
    }

    return $this;
  }
  
  
  
  // Accessors
  
  public function has($type, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    $config = $this->getBackend()->get($type, $profile);

    if( !is_array($config) || !isset($config['class']) ) {
      return false;
    } else {
      return true;
    }
  }
  
  public function hasForRegistry($spec)
  {
    if( false !== strpos($spec, '-') ) {
      list($type, $profile) = explode('-', $spec, 2);
    } else {
      $type = $spec;
      $profile = null;
    }
    
    return $this->has($type, $profile);
  }
  
  public function factory($type, $profile = null)
  {
    //if( is_numeric($type) ) {
    //  $key = 'service-' . $type;
    //} else {
      $key = $type . '-' . ( $profile ? $profile : 'default' );
    //}
    
    $config = $this->getBackend()->get($type, $profile);

    if( !is_array($config) || !isset($config['class']) ) {
      throw new Engine_ServiceLocator_Exception(sprintf('No configuration for %s', $key));
    }
      
    // Load class
    $class = $config['class'];
    if( !empty($config['path']) && !class_exists($class, false) ) {
      // Try to include
      include_once $config['path'];
    }
    if( !class_exists($class, true) ) {
      throw new Engine_ServiceLocator_Exception(sprintf('Unable to load class %s for %s', $class, $key));
    }

    // Params
    if( empty($config['factory']) ) {
      // Constructor
      try {
        if( !empty($config['options']) && is_array($config['options']) ) {
          $object = new $class($config['options']);
        } else if( !empty($config['args']) ) {
          if( is_scalar($config['args']) ) {
            $object = new $class($config['args']);
          } else if( is_array($config['args']) && count($config['args']) === 1 ) {
            $object = new $class($config['args'][0]);
          } else if( is_array($config['args']) ) {
            $r = new ReflectionClass($class);
            $object = $r->newInstanceArgs($config['args']);
          } else {
            $object = null;
          }
        } else {
          $object = new $class();
        }
      } catch( Exception $e ) {
        throw new Engine_ServiceLocator_Exception(sprintf('Error trying to load class %s for %s', $class, $key));
      }
    } else {
      // Factory
      if( !is_string($config['factory']) ) {
        throw new Engine_ServiceLocator_Exception(sprintf('Invalid factory method while trying to load class %s for %s', $class, $key));
      }
      $method = $config['factory'];
      if( !empty($config['options']) && is_array($config['options']) ) {
        $object = call_user_func(array($class, $method), $config['options']);
      } else if( !empty($config['args']) ) {
        if( is_scalar($config['args']) ) {
          $object = call_user_func(array($class, $method), $config['args']);
        } else if( is_array($config['args']) && count($config['args']) === 1 ) {
          $object = call_user_func(array($class, $method), $config['args'][0]);
        } else if( is_array($config['args']) ) {
          $object = call_user_func_array(array($class, $method), $config['args']);
        } else {
          $object = null;
        }
      } else {
        $object = new $class();
      }
    }

    if( !$object ) {
      throw new Engine_ServiceLocator_Exception(sprintf('Unknown configuration format for class %s for %s', $class, $key));
    } else if( !@is_a($object, $class) ) {
      throw new Engine_ServiceLocator_Exception(sprintf('Invalid object loaded for class %s for %s', $class, $key));
    }

    return $object;
  }
  
  /**
   * Get the resource as specified by $type and $profile
   * 
   * @param string $type
   * @param string $profile
   * @return mixed
   */
  public function load($type, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    if( !Engine_Registry::isRegistered($key) ) {
      $object = $this->factory($type, $profile);
      
      if( !$object ) {
        throw new Engine_ServiceLocator_Exception(sprintf('Unknown configuration format for class %s for %s', $class, $key));
      }

      Engine_Registry::set($key, $object);
    }
    
    return Engine_Registry::get($key);
  }
  
  /**
   * Load a resource based for the registry object
   * 
   * @param string $spec
   * @return mixed
   */
  public function loadForRegistry($spec)
  {
    if( false !== strpos($spec, '-') ) {
      list($type, $profile) = explode('-', $spec, 2);
    } else {
      $type = $spec;
      $profile = null;
    }
    
    return $this->factory($type, $profile);
  }
}
