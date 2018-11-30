<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Interface.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
interface Engine_Vfs_Object_Interface
{
  public function __construct(Engine_Vfs_Adapter_Interface $adapter, $path, $mode = 'r');

  public function getPath();

  public function getMode();
  
  public function getResource();

  public function getFileInfo();
  
  public function open($mode = 'r');

  public function end();

  public function flush();

  public function read($length);

  public function rewind();

  public function seek($offset, $whence = SEEK_SET);

  public function stat();

  public function tell();

  public function truncate($size);

  public function write($str, $length = null);
}