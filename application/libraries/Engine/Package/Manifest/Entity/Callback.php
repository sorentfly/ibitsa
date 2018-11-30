<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Callback.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_Callback extends Engine_Package_Manifest_Entity_Abstract
{
  protected $_path;
  
  protected $_class;

  protected $_priority = 100;

  protected $_props = array(
    'path',
    'class',
    'priority',
  );

  public function __construct($spec, $options = null)
  {
    if( is_array($spec) ) {
      $options = array_merge($spec, (array) $options);
    }
    if( is_array($options) ) {
      $this->setOptions($options);
    }
    if( is_string($spec) ) {
      $this->setType($spec);
    }
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function setPath($path)
  {
    $this->_path = (string) $path;
    return $this;
  }

  public function setClass($class)
  {
    $this->_class = (string) $class;
    return $this;
  }

  public function getClass()
  {
    return $this->_class;
  }

  public function setPriority($priority)
  {
    $this->_priority = (integer) $priority;
    return $this;
  }

  public function getPriority()
  {
    return $this->_priority;
  }
}
