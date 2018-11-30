<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bzip2.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Stream/Abstract.php';
//require_once 'Engine/Stream/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Stream_Bzip2 extends Engine_Stream_Abstract
{
  public function stream_close()
  {
    return bzclose($this->_resource);
  }

  public function stream_eof()
  {
    return !$this->_resource || feof($this->_resource);
  }

  public function stream_flush()
  {
    return bzflush($this->_resource);
  }

  public function stream_lock($operation)
  {
    return flock($this->_resource, $operation); // @todo test
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    if( !function_exists('bzopen') ) {
      return false;
    }

    $resource = bzopen($path, $mode);
    if( !$resource ) {
      return false;
    }

    $this->_path = $path;
    $this->_mode = $mode;
    $this->_resource = $resource;
  }

  public function stream_read($count)
  {
    return bzread($this->_resource, $count);
  }

  public function stream_seek($offset, $whence)
  {
    return false;
  }

  public function stream_set_option($option, $arg1, $arg2)
  {
    return false;
  }

  public function stream_stat()
  {
    return fstat($this->_resource); // @todo test
  }

  public function stream_tell()
  {
    return false;
    //return ftell($this->_resource);
  }

  public function stream_write($data)
  {
    return bzwrite($this->_resource, $data, strlen($data));
  }
}