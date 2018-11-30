<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Stream/Interface.php';
//require_once 'Engine/Stream/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Stream_Abstract implements Engine_Stream_Interface
{
  public $context;

  protected $_path;

  protected $_mode;

  protected $_options;

  protected $_opened_path;

  protected $_resource;

  public function __construct($path = null, $mode = null, $options = 0, &$opened_path = null)
  {
    if( null !== $path ) {
      $this->stream_open($path, $mode, $options, $opened_path);
    }
  }

  public function __call($method, array $args = null)
  {
    throw new Engine_Stream_Exception(sprintf('Method "%s" not implemented.', $method));
  }

  public function getResource()
  {
    return $this->_resource;
  }

  public function stream_flush()
  {
    return false;
  }

  public function stream_lock($operation)
  {
    return false;
  }

  public function stream_set_option($option, $arg1, $arg2)
  {
    return false;
  }

  public function stream_stat()
  {
    return false;
  }



  // Custom
  
  public function stream_copy(Engine_Stream_Interface $stream)
  {
    while( !($eof = $stream->stream_eof()) ) {
      $data = $stream->stream_read(1024);
      $this->stream_write($data);
    }
    return true;
  }



  // Utility

  protected function _setOptions($path = null, $mode = null, $options = 0, &$opened_path = null)
  {
    $this->_path = $path;
    $this->_mode = $mode;
    $this->_opened_path = &$opened_path;
  }
}