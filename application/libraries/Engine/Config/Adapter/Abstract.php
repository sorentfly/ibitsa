<?php

abstract class Engine_Config_Adapter_Abstract implements ArrayAccess
{
  /*
  const CACHE_KEY = 0;
  const CACHE_ALL = 1;
  */
  
  /**
   * @var Zend_Cache_Core
   */
  protected $_cache;
  
  /**
   *
   * @var string
   */
  protected $_cacheKey;
  /**
   * @var integer
   */
  /*
  protected $_cacheMode = self::CACHE_KEY;
  */
  
  /**
   * @var array
   */
  protected $_data = null;
  
  
  
  // Main
  
  public function __construct(array $options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
    
    $this->_cacheKey = get_class($this);
  }
  
  
  
  // Options
  
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
  
  
  
  // Cache
  
  static public function getDefaultCache()
  {
    if( Engine_Registry::isRegistered('cache-default') ) {
      return Engine_Registry::get('cache-default');
    }
  }
  
  public function getCache()
  {
    if( null === $this->_cache ) {
      $this->_cache = self::getDefaultCache();
    }
    return $this->_cache;
  }
  
  public function setCache(Zend_Cache_Core $cache)
  {
    /*
    $this->_cacheMode = self::CACHE_ALL;
    if( $cache instanceof Zend_Cache_Core ) {
      if( $cache->getBackend() instanceof Zend_Cache_Backend_Apc ) {
        $this->_cacheMode = self::CACHE_KEY;
      }
    }
    */
    $this->_cache = $cache;
    return $this;
  }
  
  
  // Magic
  
  public function __isset($key)
  {
    return $this->isset($key);
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function __set($key, $value)
  {
    return $this->set($key, $value);
  }
  
  public function __unset($key)
  {
    return $this->unset($key);
  }
  
  
  
  // ArrayAccess
  
  public function offsetExists($key)
  {
    return $this->has($key);
  }
  
  public function offsetGet($key)
  {
    return $this->get($key);
  }
  
  public function offsetSet($key, $value)
  {
    return $this->set($key, $value);
  }
  
  public function offsetUnset($key)
  {
    return $this->unset($key);
  }
  
  
  
  // Abstract
  
  abstract public function get($key);
  
  abstract public function has($key);
  
  abstract public function set($key, $value);
  
  abstract public function remove($key);
}