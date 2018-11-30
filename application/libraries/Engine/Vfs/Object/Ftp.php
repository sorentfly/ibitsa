<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ftp.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Object/Abstract.php';
//require_once 'Engine/Vfs/Object/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Object_Ftp extends Engine_Vfs_Object_Abstract
{
  protected $_tmpFile;
  
  public function open($mode = 'r')
  {
    // Create temporary file
    if( null === $this->_tmpFile ) {
      $this->_tmpFile = tempnam('/tmp', 'engine_vfs_object');
      if( !$this->_tmpFile ) {
        throw new Engine_Vfs_Object_Exception('Unable to create temporary file');
      }
    }

    // Transfer remote file to temporary file
    $this->_adapter->get($this->_tmpFile, $this->_path);

    // Open temporary file
    $resource = fopen($this->_tmpFile, $mode);
    if( !$resource ) {
      throw new Engine_Vfs_Object_Exception(sprintf('Unable to open file "%s" in mode "%s"', $this->_path, $mode));
    }

    $this->_resource = $resource;
    return $this;
  }
  
  public function close()
  {
    // Flush first (to get it to re-upload)
    $this->flush();
    // Close
    $ret = fclose($this->getResource());
    $this->_resource = null;
    return $ret;
  }

  public function end()
  {
    return feof($this->getResource());
  }

  public function flush()
  {
    $ret = fflush($this->getResource());
    // Also send back to server
    $ret &= $this->_adapter->put($this->_path, $this->_tmpFile);
    return $ret;
  }

  public function read($length)
  {
    return fread($this->getResource(), $length);
  }

  public function rewind()
  {
    return rewind($this->getResource());
  }

  public function seek($offset, $whence = SEEK_SET)
  {
    return fseek($this->getResource(), $offset, $whence);
  }

  public function stat()
  {
    throw new Engine_Vfs_Object_Exception(sprintf('Method %s is not implemented', __METHOD__));
    //return fstat($this->getResource());
  }

  public function tell()
  {
    return ftell($this->getResource());
  }

  public function truncate($size)
  {
    return ftruncate($this->getResource(), $size);
  }

  public function write($string, $length = null)
  {
    if( null === $length ) {
      return fwrite($this->getResource(), $string);
    } else {
      return fwrite($this->getResource(), $string, $length);
    }
  }
}