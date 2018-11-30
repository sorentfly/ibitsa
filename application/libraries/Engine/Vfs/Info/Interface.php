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
interface Engine_Vfs_Info_Interface
{
  // General
  
  public function __construct(Engine_Vfs_Adapter_Interface $adapter, $path, array $info = null);

  public function getAdapter();

  public function reload();



  // Tree

  public function getParent();

  public function getChildren();


  
  // Path

  public function getPath();

  public function getBaseName();

  public function getDirectoryName();

  public function getRealPath();
  
  public function toString();

  public function __toString();



  // General

  public function exists();

  public function getType();

  public function isDirectory();

  public function isFile();

  public function isLink();

  

  // Stat

  public function getUid();

  public function getGid();

  public function getSize();
  
  public function getAtime();

  public function getMtime();

  public function getCtime();



  // Perms
  
  public function getRights();

  public function isExecutable();

  public function isReadable();

  public function isWritable();
  


  // Object
  
  public function open($mode = 'r');
}