<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Stream.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Stream/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Stream
{
  protected static $_wrappers;

  public $context; // Not used

  protected $_file;



  // Static
  
  static public function registerWrapper(Engine_Vfs_Adapter_Interface $adapter, $protocol = null, $flags = 0)
  {
    if( null === $protocol && method_exists($adapter, 'getStreamProtocol') ) {
      $protocol = $adapter->getStreamProtocol();
    }

    if( version_compare(PHP_VERSION, '5.2.4', '>=') ) {
      $return = stream_wrapper_register($protocol, __CLASS__);
    } else {
      $return = stream_wrapper_register($protocol, __CLASS__, $flags);
    }

    if( !$return ) {
      throw new Engine_Vfs_Stream_Exception(sprintf('Unable to register stream, protocol "%s" is already registered', $protocol));
    }

    self::$_wrappers[$protocol] = $adapter;

    return $return;
  }

  static public function unregisterWrapper($protocol)
  {
    if( !isset(self::$_wrappers[$protocol]) ) {
      throw new Engine_Vfs_Stream_Exception(sprintf('Unable to unregister stream, protocol "%s" is not registered to a vfs adapter', $protocol));
    }

    $return = stream_wrapper_unregister($protocol);

    if( !$return ) {
      throw new Engine_Vfs_Stream_Exception(sprintf('Unable to unregister stream, protocol "%s" is not registered', $protocol));
    }

    unset(self::$_wrappers[$protocol]);

    return $return;
  }

  static public function getWrapper($protocol)
  {
    if( isset(self::$_wrappers[$protocol]) ) {
      return self::$_wrappers[$protocol];
    }
    return null;
  }



  // Stream

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    throw new Engine_Vfs_Stream_Exception('Not yet implemented');
  }
}