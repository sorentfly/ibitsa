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

//require_once 'Engine/Vfs/Info/Interface.php';
//require_once 'Engine/Vfs/Info/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Info_Standard implements Engine_Vfs_Info_Interface
{
  protected $_adapter;

  protected $_path;

  protected $_info;

  public function __construct(Engine_Vfs_Adapter_Interface $adapter, $path, array $info = null)
  {
    $this->_adapter = $adapter;
    $this->_path = $path;
    $this->_info = $info;
    $this->init();
  }

  public function __sleep()
  {
    return array('_path', '_info');
  }

  public function init()
  {

  }

  public function getAdapter()
  {
    if( null === $this->_adapter ) {
      throw new Engine_Vfs_Info_Exception('No adapter registered. This object may have been serialized');
    }
    return $this->_adapter;
  }

  public function getInfo()
  {
    return $this->_info;
  }

  public function reload()
  {
    $this->_info = $this->getAdapter()->stat($this->_path);
  }



  // Tree

  public function getParent()
  {
    return $this->getAdapter()->info($this->getDirectoryName());
  }

  public function getChildren()
  {
    if( !$this->isDirectory() ) {
      return false;
    }

    return $this->getAdapter()->directory($this->_path);
  }



  // Path

  public function getPath()
  {
    return $this->_path;
  }

  public function getBaseName()
  {
    return basename($this->_path);
  }

  public function getDirectoryName()
  {
    return dirname($this->_path);
  }

  public function getRealPath()
  {
    // Note: most of the time it will be real already
    return $this->_path;
  }

  public function toString()
  {
    return $this->_path;
  }

  public function __toString()
  {
    return $this->_path;
  }



  // General
  
  public function exists()
  {
    return (bool) @$this->_info['exists'];
  }

  public function getType()
  {
    return @$this->_info['type'];
  }

  public function isDirectory()
  {
    return ( @$this->_info['type'] == 'dir' );
  }

  public function isFile()
  {
    return ( @$this->_info['type'] == 'file' );
  }

  public function isLink()
  {
    return ( @$this->_info['type'] == 'link' );
  }



  // Stat

  public function getUid()
  {
    return @$this->_info['uid'];
  }

  public function getGid()
  {
    return @$this->_info['gid'];
  }

  public function getSize()
  {
    return @$this->_info['size'];
  }

  public function getAtime()
  {
    return @$this->_info['atime'];
  }

  public function getMtime()
  {
    return @$this->_info['mtime'];
  }

  public function getCtime()
  {
    return @$this->_info['ctime'];
  }



  // Perms

  public function getRights()
  {
    return @$this->_info['rights'];
  }

  public function isExecutable()
  {
    if( !isset($this->_info['executable']) ) {
      $this->_info['executable'] = $this->getAdapter()->checkPerms(0x001, $this->getRights(), $this->getUid(), $this->getGid());
    }
    return (bool) $this->_info['executable'];
  }

  public function isReadable()
  {
    if( !isset($this->_info['readable']) ) {
      $this->_info['readable'] = $this->getAdapter()->checkPerms(0x004, $this->getRights(), $this->getUid(), $this->getGid());
    }
    return (bool) $this->_info['readable'];
  }

  public function isWritable()
  {
    if( !isset($this->_info['writable']) ) {
      $this->_info['writable'] = $this->getAdapter()->checkPerms(0x002, $this->getRights(), $this->getUid(), $this->getGid());
    }
    return (bool) $this->_info['writable'];
  }


  
  // Object

  public function open($mode = 'r')
  {
    return $this->getAdapter()->object($this->_path, $mode);
  }
}