<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: System.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Adapter/Abstract.php';
//require_once 'Engine/Vfs/Adapter/LocalAbstract.php';
//require_once 'Engine/Vfs/Adapter/Exception.php';
//require_once 'Engine/Vfs/Directory/Standard.php';
//require_once 'Engine/Vfs/Info/Standard.php';
//require_once 'Engine/Vfs/Object/System.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Adapter_System extends Engine_Vfs_Adapter_LocalAbstract
{
  public function __construct(array $config = null)
  {
    parent::__construct($config);
    $this->_directorySeparator = DIRECTORY_SEPARATOR;
  }

  public function getResource()
  {
    return $this;
  }



  // Informational

  public function exists($path)
  {
    $path = $this->path($path);
    
    return file_exists($path);
  }

  public function isDirectory($path)
  {
    $path = $this->path($path);

    return is_dir($path);
  }

  public function isFile($path)
  {
    $path = $this->path($path);

    return is_file($path);
  }

  public function getSystemType()
  {
    if( null === $this->_systemType ) {
      $systype = php_uname('s');
      $this->_systemType = self::processSystemType($systype);
    }
    return $this->_systemType;
  }
  
  public function stat($path)
  {
    $path = $this->path($path);
    $stat = stat($path);

    // Missing
    if( !$stat ) {
      return array(
        'name' => basename($path),
        'path' => $path,
        'exists' => false,
      );
    }

    // Get extra
    $type = filetype($path);
    $rights = substr(sprintf('%o', fileperms($path)), -4);

    // Process stat
    $info = array(
      // General
      'name' => basename($path),
      'path' => $path,
      'exists' => true,
      'type' => $type,

      // Stat
      'uid' => $stat['uid'],
      'gid' => $stat['gid'],
      'size' => $stat['size'],
      'atime' => $stat['atime'],
      'mtime' => $stat['mtime'],
      'ctime' => $stat['ctime'],

      // Perms
      'rights' => $rights,
      'readable' => is_readable($path),
      'writable' => is_writable($path),
      'executable' => is_executable($path),
    );

    return $info;
  }


  
  // General

  public function copy($sourcePath, $destPath)
  {
    $sourcePath = $this->path($sourcePath);
    $destPath = $this->path($destPath);

    $return = @copy($sourcePath, $destPath);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to copy "%s" to "%s"', $sourcePath, $destPath));
    }

    return $return;
  }

  public function get($local, $path)
  {
    $path = $this->path($path);

    $return = @copy($path, $local);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get "%s" to "%s"', $path, $local));
    }

    return $return;
  }

  public function getContents($path)
  {
    $path = $this->path($path);

    $return = @file_get_contents($path);

    if( false === $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get contents of "%s"', $path));
    }

    return $return;
  }

  public function mode($path, $mode, $recursive = false)
  {
    $path = $this->path($path);
    
    $return = @chmod($path, self::processMode($mode));

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change mode on "%s"', $path));
    }

    if( $recursive ) {
      $info = $this->info($path);
      if( $info->isDirectory() ) {
        foreach( $info->getChildren() as $child ) {
          $return &= $this->mode($child->getPath(), $mode, true);
        }
      }
    }

    return $return;
  }

  public function move($oldPath, $newPath)
  {
    $oldPath = $this->path($oldPath);
    $newPath = $this->path($newPath);

    $return = @rename($oldPath, $newPath);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to rename "%s" to "%s"', $oldPath, $newPath));
    }

    return $return;
  }

  public function put($path, $local)
  {
    $path = $this->path($path);

    $return = @copy($local, $path);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put "%s" to "%s"', $local, $path));
    }

    // Apply umask
    try {
      $this->mode($path, $this->getUmask(0666));
    } catch( Exception $e ) {
      // Silence
    }

    return $return;
  }

  public function putContents($path, $data)
  {
    $path = $this->path($path);

    $return = @file_put_contents($path, $data);

    if( false === $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put contents to "%s"', $path));
    }

    // Apply umask
    try {
      $this->mode($path, $this->getUmask(0666));
    } catch( Exception $e ) {
      // Silence
    }

    return $return;
  }

  public function unlink($path)
  {
    $path = $this->path($path);

    $return = @unlink($path);

    if( false === $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to unlink "%s"', $path));
    }

    return $return;
  }



  // Directories

  public function changeDirectory($directory)
  {
    $directory = $this->path($directory);

    if( $this->isDirectory($directory) ) {
      $this->_path = rtrim($directory, '/\\');
      return true;
    } else {
      return false;
    }
  }

  public function listDirectory($directory, $details = false)
  {
    $directory = $this->path($directory);

    $children = array();
    foreach( scandir($directory) as $child ) {
      if( $child == '.' || $child == '..' ) continue;
      if( $details ) {
        $children[] = $this->stat($directory . $this->_directorySeparator . $child);
      } else {
        $children[] = $this->path($directory . $this->_directorySeparator . $child);
      }
    }

    return $children;
  }

  public function makeDirectory($directory, $recursive = false)
  {
    $directory = $this->path($directory);

    // Already a directory
    if( $this->isDirectory($directory) ) {
      return true;
    }
    
    $return = @mkdir($directory, $this->getUmask(0777), $recursive);

    if( false === $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to make directory "%s"', $directory));
    }

    return $return;
  }

  public function printDirectory()
  {
    if( null === $this->_path ) {
      $this->_path = getcwd();
    }
    return $this->_path;
  }

  public function removeDirectory($directory, $recursive = false)
  {
    $directory = $this->path($directory);

    // Recursive
    if( $recursive ) {
      $return = true;

      // Iterate over contents
      $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::CHILD_FIRST);
      foreach( $it as $key => $child ) {
        if( $child->getFilename() == '..' || $child->getFilename() == '.' ) continue;
        if( $child->isDir() ) {
          $return &= $this->removeDirectory($child->getPathname(), false);
        } else if( $it->isFile() ) {
          $return &= $this->unlink($child->getPathname(), false);
        }
      }
      $return &= $this->removeDirectory($directory, false);
    }

    // Normal
    else {
      $return = @rmdir($directory);
    }

    if( false === $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to remove directory "%s"', $directory));
    }

    return $return;
  }



  // User

  public function getUid()
  {
    if( null === $this->_uid ) {
      if( function_exists('posix_getuid') ) {
        $this->_uid = posix_getuid();
      } else {
        // Find another way to do it?
        $this->_uid = false;
      }
    }

    return $this->_uid;
  }

  public function getGid()
  {
    if( null === $this->_gid ) {
      if( function_exists('posix_getgid') ) {
        $this->_gid = posix_getgid();
      } else {
        // Find another way to do it?
        $this->_gid = false;
      }
    }

    return $this->_gid;
  }
}