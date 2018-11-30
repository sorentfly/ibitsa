<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Vfs.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs
{
  /**
   * Factory method for VFS
   * 
   * @param string $adapter
   * @param array $config
   * @return Engine_Vfs_Adapter_Interface
   */
  static public function factory($adapter, array $config = array())
  {
    $classPrefix = 'Engine_Vfs_Adapter_';
    if( isset($config['adapterPrefix']) ) {
      $classPrefix = rtrim($config['adapterPrefix'], '_') . ')';
    }

    $class = $classPrefix . ucfirst($adapter);

    Engine_Loader::loadClass($class);

    if( !is_subclass_of($class, 'Engine_Vfs_Adapter_Interface') ) {
      throw new Engine_Vfs_Exception('Adapter class must extend Engine_Vfs_Adapter_Interface');
    }

    $instance = new $class($config);

    return $instance;
  }
}
