<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Cache.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Controller_Action_Helper_Cache extends
  Zend_Controller_Action_Helper_Abstract
{
  static protected $_cache;
  
  protected $_caching = false;

  protected $_params = array();

  protected $_prefix = '';

  protected $_isCached = false;
  
  protected $_cacheLifetime = false;


  // General

  public function direct()
  {
    return $this;
  }

  public function isValid()
  {
    // Disabled
    $this->_isCached = false;
    if( !$this->_caching ) return;

    // Get cache object
    $cache = self::getCache();
    if( null == $cache )
    {
      $this->_caching = false;
      return;
    }

    // Get data from cache
    $key = $this->_generateKey();
    $data = $cache->load($key);

    // Cache hit, set in body
    if( $data )
    {
      $this->_isCached = true;
      $this->getResponse()->appendBody($data);
      $this->getRequest()->setDispatched(false);
    }

    return $this->_isCached;
  }

  public function saveData()
  {
    if( $this->_caching )
    {
      if( !$this->_isCached )
      {
        $cache = self::getCache();
        $key = $this->_generateKey();
        $data = $this->getResponse()->getBody();
        if( $cache && $data )
        {
          $cache->save($data, $key, array(), $this->_cacheLifetime);
        }
      }
      else
      {
        $this->getRequest()->setDispatched(true);
      }
    }
  }



  // Internal hooks

  public function init()
  {
    $this->_prefix = get_class($this);
  }

  public function preDispatch()
  {
    $this->isValid();
  }

  public function postDispatch()
  {
    if( $this->_caching )
    {
      $this->saveData();
    }
    
    $this->_caching = false;
    $this->_isCached = false;
    $this->_params = array();
    $this->_cacheLifetime = false;
  }



  // Options

  public function setCaching($flag = true)
  {
    $this->_caching = true;
    return $this;
  }

  public function addCacheParam($key, $value)
  {
    $this->_params[$key] = $value;
    return $this;
  }

  public function clearCacheParams()
  {
    $this->_params = array();
    return $this;
  }

  public function getParams()
  {
    return $this->_params;
  }

  public function setLifetime($time = false)
  {
    $this->_cacheLifetime = $time;
    return $this;
  }



  // Cache

  static public function setCache(Zend_Cache_Core $cache)
  {
    self::$_cache = $cache;
  }

  static public function getCache()
  {
    if( null === self::$_cache )
    {
      if( Zend_Registry::isRegistered('Zend_Cache') &&
          ($cache = Zend_Registry::get('Zend_Cache')) instanceof Zend_Cache_Core )
      {
        self::$_cache = $cache;
      }
    }
    
    return self::$_cache;
  }



  // Utility

  public function _generateKey()
  {
    ksort($this->_params);

    $key = $this->_prefix . '__'
      . get_class($this->getActionController()) . '__'
      . $this->getActionController()->getRequest()->getActionName() . '__'
      . join('_', array_values($this->_params));

    $key = preg_replace('/[^a-z0-9_]/i', '_', $key);
    return $key;
  }
}