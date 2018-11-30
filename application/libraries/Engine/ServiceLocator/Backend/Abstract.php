<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_ServiceLocator_Backend_Abstract
{
  // Properties
  
  /**
   * @var Zend_Cache_Core
   */
  protected $_cache;

  /**
   * @var array
   */
  protected $_data;
  
  
  
  // Cache

  public function setCache(Zend_Cache_Core $cache = null)
  {
    $this->_cache = $cache;
    return $this;
  }

  public function getCache()
  {
    return $this->_cache;
  }
  
  
  
  // Abstract

  /**
   * Get the resource configuration
   * 
   * @param string $type
   * @param string $profile
   * @return mixed
   */
  abstract public function get($type, $profile = null);

  /**
   * Check if the resource configuration exists
   * 
   * @param string $type
   * @param string $profile
   * @return boolean 
   */
  abstract public function has($type, $profile = null);

  /**
   * Set the resource configuration
   * 
   * @param string $type
   * @param array $value
   * @param string $profile
   * @return Engine_ServiceLocator_Backend_Abstract 
   */
  abstract public function set($type, $value, $profile = null);
}
