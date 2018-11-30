<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
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
class Engine_Stream_File extends Engine_Stream_Abstract
{
  public function stream_close()
  {
    $return = fclose($this->_resource);
    if( $return === false ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to close file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_eof()
  {
    return !$this->_resource || feof($this->_resource);
  }

  public function stream_flush()
  {
    $return = fflush($this->_resource);
    if( $return === false ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to flush file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_lock($operation)
  {
    $return = flock($this->_resource, $operation);
    if( $return === false ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to lock file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    $resource = fopen($path, $mode);
    if( !$resource ) {
      if( $options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to open file "%s" in mode "%s"', $path, $mode));
      } else {
        return false;
      }
    }
    $this->_resource = $resource;
    $this->_setOptions($path, $mode, $options, $opened_path);
    return true;
  }

  public function stream_read($count)
  {
    $return = fread($this->_resource, $count);
    if( false === $return ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to read from file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_seek($offset, $whence)
  {
    $return = fseek($this->_resource, $offset, $whence);
    if( $return === -1 || $return === false ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to seek file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_stat()
  {
    $return = @fstat($this->_resource);
    if( false === $return ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to stat file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_tell()
  {
    $return = ftell($this->_resource);
    if( false === $return ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to tell file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }

  public function stream_write($data)
  {
    $return = fwrite($this->_resource, $data, strlen($data));
    if( false === $return ) {
      if( $this->_options & self::OPT_THROW_EXCEPTIONS ) {
        throw new Engine_Stream_Exception(sprintf('Unable to write to file "%s"', $this->_path));
      } else {
        return false;
      }
    }
    return $return;
  }
}