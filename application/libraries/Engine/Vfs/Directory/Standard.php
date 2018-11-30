<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Standard.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Directory/Interface.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Directory_Standard implements Engine_Vfs_Directory_Interface
{
  protected $_adapter;

  protected $_path;
  
  protected $_contents;

  protected $_position;

  public function __construct(Engine_Vfs_Adapter_Interface $adapter, $path, array $contents = null)
  {
    $this->_adapter = $adapter;
    $this->_path = $path;
    $this->_position = 0;
    $this->_contents = array();
    
    // Check contents
    foreach( (array) $contents as $content ) {
      $adapterClass = get_class($this->_adapter);
      if( is_string($content) ) {
        $content = $this->_adapter->info($content);
      }
      if( !($content instanceof Engine_Vfs_Info_Interface) ) {
        // Throw or ignore?
        continue;
      }
      // Wrong adapter
      if( get_class($content->getAdapter()) != $adapterClass ) {
        // Throw or ignore?
        continue;
      }
      $this->_contents[] = $content;
    }
  }

  public function getAdapter()
  {
    return $this->_adapter;
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function toArray()
  {
    return $this->_contents;
  }


  
  // Iterator

  public function current()
  {
    return $this->_contents[$this->_position];
  }

  public function key()
  {
    return $this->_position;
  }

  public function next()
  {
    ++$this->_position;
  }
  public function rewind()
  {
    $this->_position = 0;
  }

  public function valid()
  {
    return isset($this->_contents[$this->_position]);
  }

  public function seek($position)
  {
    if( !is_int($position) || !isset($this->_contents[$position]) ) {
      throw new OutOfBoundsException('Seeking out of bounds in Engine_Vfs_Directory_System');
    }
    $this->_position = $position;
  }
}