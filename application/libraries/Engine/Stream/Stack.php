<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Stack.php 9747 2012-07-26 02:08:08Z john $
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
class Engine_Stream_Stack extends Engine_Stream_Abstract
{
  protected $_path;

  protected $_mode;

  protected $_data;

  static public function registerWrapper()
  {
    self::unregisterWrapper();
    stream_wrapper_register('stack', __CLASS__);
  }

  static public function unregisterWrapper()
  {
    $existed = in_array('stack', stream_get_wrappers());
    if( $existed ) {
      stream_wrapper_unregister('stack');
    }
  }

  public function stream_close()
  {
    $this->_path = null;
    $this->_mode = null;
    $this->_data = null;
    return true;
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    $this->_path = $path;
    $this->_mode = $mode;
    $this->_data = '';

    return true;
  }

  public function stream_read($count)
  {
    $ret = substr($this->_data, 0, $count);
    $this->_data = substr($this->_data, strlen($ret));
    return $ret;
  }

  public function stream_write($data)
  {
    $this->_data .= $data;
    return strlen($data);
  }

  public function stream_tell()
  {
    return 0;
  }

  public function stream_seek($offset, $whence)
  {
    // This actually doesn't do anything, should we return false?
    return true;
  }

  public function stream_eof()
  {
    return ( '' === $this->_data );
  }
}