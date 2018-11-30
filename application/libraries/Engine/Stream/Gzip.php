<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gzip.php 9747 2012-07-26 02:08:08Z john $
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
class Engine_Stream_Gzip extends Engine_Stream_Abstract
{
  public $context;

  protected $_path;

  protected $_mode;

  protected $_resource;

  public function stream_close()
  {
    return gzclose($this->_resource);
  }

  public function stream_eof()
  {
    return !$this->_resource || gzeof($this->_resource);
  }

  public function stream_flush()
  {
    return fflush($this->_resource); // @todo test
  }

  public function stream_lock($operation)
  {
    return flock($this->_resource, $operation); // @todo test
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    if( !function_exists('gzopen') ) {
      return false;
    }

    $resource = gzopen($path, $mode);
    if( !$resource ) {
      return false;
    }

    $this->_path = $path;
    $this->_mode = $mode;
    $this->_options = (integer) $options;
    $this->_resource = $resource;
  }

  public function stream_read($count)
  {
    return gzread($this->_resource, $count);
  }

  public function stream_seek($offset, $whence)
  {
    return gzseek($this->_resource, $offset, $whence);
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
    return gztell($this->_resource);
  }

  public function stream_write($data)
  {
    return gzwrite($this->_resource, $data, strlen($data));
  }
}