<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Object/Interface.php';
//require_once 'Engine/Vfs/Object/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Vfs_Object_Abstract implements Engine_Vfs_Object_Interface
{
  protected $_adapter;

  protected $_path;

  protected $_mode;

  protected $_resource;

  public function __construct(Engine_Vfs_Adapter_Interface $adapter, $path, $mode = 'r')
  {
    $this->_adapter = $adapter;
    $this->_path = $path;
    $this->_mode = $mode;
    $this->open($mode);
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function getMode()
  {
    return $this->_mode;
  }

  public function getResource()
  {
    if( !$this->_resource ) {
      throw new Engine_Vfs_Object_Exception('No resource');
    }
    return $this->_resource;
  }

  public function getFileInfo()
  {
    return $this->_adapter->info($this->_path);
  }
}