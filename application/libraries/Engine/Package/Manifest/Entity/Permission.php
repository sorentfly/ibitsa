<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Permission.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_Permission extends Engine_Package_Manifest_Entity_Abstract
{
  protected $_path;

  protected $_mode;

  protected $_recursive = false;

  protected $_inclusive = true;

  protected $_props = array(
    'path',
    'mode',
    'recursive',
    'inclusive',
  );

  public function __construct($spec, $options = null)
  {
    if( is_array($spec) ) {
      $this->fromArray($spec);
    }
    if( is_array($options) ) {
      $this->setOptions($options);
    }
    if( is_string($spec) ) {
      $this->setPath($spec);
    }
  }

  public function getPath()
  {
    if( null === $this->_path ) {
      throw new Engine_Package_Manifest_Exception('Path cannot be empty');
    }
    return $this->_path;
  }

  public function setPath($path)
  {
    $this->_path = $path;
    return $this;
  }

  public function getMode()
  {
    if( null === $this->_mode ) {
      throw new Engine_Package_Manifest_Exception('Mode cannot be empty');
    }
    return $this->_mode;
  }

  public function setMode($mode)
  {
    $this->_mode = $mode;
    return $this;
  }

  public function getRecursive()
  {
    return (bool) $this->_recursive;
  }

  public function setRecursive($recursive)
  {
    $this->_recursive = (bool) $recursive;
    return $this;
  }

  public function getInclusive()
  {
    return (bool) $this->_inclusive;
  }

  public function setInclusive($inclusive)
  {
    $this->_inclusive = (bool) $inclusive;
    return $this;
  }
}