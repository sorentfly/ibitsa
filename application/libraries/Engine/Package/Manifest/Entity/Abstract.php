<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_Abstract
{
  protected $_basePath;
  
  protected $_props;


  
  // General

  public function __construct($spec)
  {
    if( is_array($spec) ) {
      $this->setOptions($spec);
    }
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }
  
  public function getEntityType()
  {
    return strtolower(ltrim(strrchr(get_class($this), '_'), '_'));
  }

  public function setBasePath($path)
  {
    $this->_basePath = $path;
    return $this;
  }

  public function getBasePath()
  {
    if( empty($this->_basePath) ) {
      if( defined('APPLICATION_PATH') && is_dir(APPLICATION_PATH) ) {
        $this->_basePath = APPLICATION_PATH;
      } else {
        throw new Engine_Package_Manifest_Exception('base path cannot be empty');
      }
    }
    return $this->_basePath;
  }



  // Data
  
  public function toArray()
  {
    $array = array();
    if( isset($this->_props) && is_array($this->_props) ) {
      foreach( $this-> _props as $key ) {
        $method = 'get' . ucfirst($key);
        if( method_exists($this, $method) ) {
          $pval = $this->$method();
          $array[$key] = $pval;
        }
      }
    }
    return $array;
  }

  public function fromArray($array)
  {
    $this->setOptions($array);
    return $this;
  }

  public function addToArchive(Archive_Tar $archive)
  {
    // Do nothing
  }


  // Static

  static public function build($directory)
  {
    $list = array();
    $unique = array();

    // Build
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::SELF_FIRST);
    while( $it->valid() ) {
      $key = $it->key();
      // Make sure it's unique, Skip .svn files
      if( isset($unique[$key]) || stripos($key, '.svn') !== false ) {
        $it->next();
        continue;
      }
      $unique[$key] = true;

      // Add
      $subpath = $it->getSubPathName();

      // Skip dot files, package files and .svn or CVS folders
      if( !$it->isDot() &&
          substr(basename($subpath), 0, strrpos(basename($subpath), '.')) != 'package' &&
          basename($subpath) != '.svn' &&
          basename($subpath) != 'CVS' ) {
        $key = $it->key();
        //$list[$it->getSubPathName()] = array(
        $list[] = array(
          'path' => self::fix_path($it->getSubPathName()),
          'dir' => $it->isDir(),
          'file' => $it->isFile(),
          'perms' => substr(sprintf('%o', $it->getPerms()), -4), // @todo test on windows
          'size' => $it->getSize(),
          'sha1' => ( $it->isFile() ? sha1_file($key) : null),
        );
      }
      $it->next();
    }

    ksort($list);

    return $list;
  }

  static public function build_file($file)
  {
    if( !file_exists($file) /* || !is_file($file) */ ) {
      throw new Engine_Package_Exception(sprintf('File does not exist: %s', $file));
    }
    return array(
      'path' => basename($file),
      'dir' => is_dir($file),
      'file' => is_file($file),
      'perms' => substr(sprintf('%o', fileperms($file)), -4), // @todo test on windows
      'size' => filesize($file),
      'sha1' => sha1_file($file),
    );
  }

  static public function sanitize_path($path)
  {
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $path));
  }

  static public function fix_path($path)
  {
    return str_replace(array('/', '\\'), '/', $path);
  }
}