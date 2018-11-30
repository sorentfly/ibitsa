<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Buffer.php 9747 2012-07-26 02:08:08Z john $
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
class Engine_Stream_Buffer extends Engine_Stream_Abstract
{
  protected $_path;
  
  protected $_mode;

  protected $_position;

  protected $_data;
  
  static public function registerWrapper()
  {
    stream_wrapper_register('buffer', __CLASS__);
  }

  public function stream_close()
  {
    $this->_path = null;
    $this->_mode = null;
    $this->_position = 0;
    $this->_data = null;
    return true;
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    $this->_path = $path;
    $this->_mode = $mode;
    $this->_position = 0;
    $this->_data = '';
    return true;
  }

  public function stream_eof()
  {
    return $this->_position >= strlen($this->_data);
  }

  public function stream_read($count)
  {
    $ret = substr($this->_data, $this->_position, $count);
    $this->_position += strlen($ret);
    return $ret;
  }

  public function stream_write($data)
  {
    $dataLen = strlen($data);
    $left = substr($this->_data, 0, $this->_position);
    $right = substr($this->_data, $this->_position + $dataLen);
    $this->_data = $left . $data . $right;
    $this->_position += $dataLen;
    return $dataLen;
  }

  public function stream_tell()
  {
    return $this->_position;
  }

  public function stream_seek($offset, $whence)
  {
    switch ($whence) {
      case SEEK_SET:
        if ($offset < strlen($this->_data) && $offset >= 0) {
          $this->_position = $offset;
          return true;
        } else {
          return false;
        }
        break;

      case SEEK_CUR:
        if ($offset >= 0) {
          $this->_position += $offset;
          return true;
        } else {
          return false;
        }
        break;

      case SEEK_END:
        if( strlen($this->_data) + $offset >= 0 ) {
          $this->_position = strlen($this->_data) + $offset;
          return true;
        } else {
          return false;
        }
        break;

      default:
        return false;
    }
  }
}