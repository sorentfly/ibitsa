<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ftp.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Adapter/Abstract.php';
//require_once 'Engine/Vfs/Adapter/RemoteAbstract.php';
//require_once 'Engine/Vfs/Adapter/Exception.php';
//require_once 'Engine/Vfs/Directory/Standard.php';
//require_once 'Engine/Vfs/Info/Standard.php';
//require_once 'Engine/Vfs/Object/Ftp.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Adapter_Ftp extends Engine_Vfs_Adapter_RemoteAbstract
{
  protected $_port = 21;
  
  protected $_useSsl = false;

  protected $_lsPatterns;

  protected $_lsMatcher;


  
  // General
  
  public function __construct(array $config = null)
  {
    if( !extension_loaded('ftp') ) {
      if( !function_exists('ftp_connect') ) { // This should be added by PEAR
        //throw new Engine_Vfs_Adapter_Exception('The ftp extension is not installed, unable to initialize FTP-VFS');
        include 'PEAR/Net/FTP/Socket.php';
        if( !function_exists('ftp_connect') ) {
          throw new Engine_Vfs_Adapter_Exception('The ftp extension is not installed and unable to load compatibility layer, unable to initialize FTP-VFS');
        }
      }
    }
    $this->_directorySeparator = '/';
    $this->_lsPatterns = array(
      'unix'    => array(
        'pattern' => '/(?:(d)|.)([rwxts-]{9})\s+(\w+)\s+([\w\d-()?.]+)\s+'.
                     '([\w\d-()?.]+)\s+(\w+)\s+(\S+\s+\S+\s+\S+)\s+(.+)/',
        'map'     => array(
            'is_dir'        => 1,
            'rights'        => 2,
            'files_inside'  => 3,
            'user'          => 4,
            'group'         => 5,
            'size'          => 6,
            'date'          => 7,
            'name'          => 8,
        )
      ),
      'windows' => array(
        'pattern' => '/([0-9\-]+)\s+([0-9:APM]+)\s+((<DIR>)|\d+)\s+(.+)/',
        'map'     => array(
            'date'   => 1,
            'time'   => 2,
            'size'   => 3,
            'is_dir' => 4,
            'name'   => 5,
        )
      )
    );
    
    parent::__construct($config);

    // Set the umask
    $this->setUmask($this->getUmask());

    // Force connect now?
    $this->getResource();
  }

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_useSsl', '_lsPatterns', '_lsMatcher'
    ));
  }
  
  public function setUseSsl($useSsl)
  {
    $this->_useSsl = (bool) $useSsl;
    return $this;
  }

  public function getUseSsl()
  {
    return $this->_useSsl;
  }
  
  public function getFileMode($filename)
  {
    return FTP_BINARY; // @todo
  }

  public function setUmask($umask)
  {
    parent::setUmask($umask);
    try {
      $this->site('UMASK ' . sprintf('%o', $this->getUmask()));
    } catch( Exception $e ) {
      // Silence
    }
    return $this;
  }


  
  // Connection
  
  public function connect()
  {
    $useSsl = $this->getUseSsl();

    if( !$useSsl ) {
      $resource = @ftp_connect($this->getHost(), $this->getPort(), $this->getTimeout());
    } else {
      if( !function_exists('ftp_ssl_connect') ) {
        throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to connect to "%s" using FTPS, PHP was not build with OpenSSL', $this->getHost()));
      } else {
        $resource = ftp_ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout());
      }
    }
    
    if( !$resource ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to connect to "%s"', $this->getHost()));
    }

    $this->_resource = $resource;

    return $this;
  }

  public function disconnect()
  {
    if( null !== $this->_resource ) {
      $return = @ftp_close($this->_resource);
      if( !$return ) {
        throw new Engine_Vfs_Adapter_Exception('Disconnect failed.');
      }
      $this->_resource = null;
    }
    
    return $this;
  }

  public function login()
  {
    // Don't try if no username supplied
    if( null === $this->getUsername() ) {
      return $this;
    }

    // Try to login
    $return = @ftp_login($this->getResource(), $this->getUsername(), $this->getPassword());
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception('Login failed.');
    }

    return $this;
  }

  public function site($command)
  {
    $ret = @ftp_site($this->getResource(), $command);

    if( !$ret ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to execute SITE command: %s', $command));
    }

    return $ret;
  }



  // Informational

  public function exists($path)
  {
    $path = $this->path($path);

    $base = basename($path);
    $parent = dirname($path);

    $return = @ftp_nlist($this->getResource(), $parent);

    if( !$return || !is_array($return) || empty($return) || (!in_array($path, $return) && !in_array($base, $return)) ) {
      return false;
    } else {
      return true;
    }
    /*
    $path = $this->path($path);
    $base = basename($path);
    $parent = dirname($path);

    try {
      $directory = $this->directory($parent);
    } catch( Exception $e ) {
      return false;
    }

    if( !$directory instanceof Engine_Vfs_Directory_Interface ) {
      return false;
    }

    foreach( $directory as $child ) {
      if( $child->getPath() == $path ) {
        return true;
      }
    }

    return false;
    */
  }

  public function isDirectory($path)
  {
    $path = $this->path($path);
    
    $pwd = $this->printDirectory();
    if( !$pwd ) {
      $pwd = '/';
      //throw new Engine_Vfs_Adapter_Exception('No pwd');
    }

    try {
      $this->changeDirectory($path);
      $isDir = true;
    } catch( Exception $e ) {
      $isDir = false;
    }

    // Restore
    $this->changeDirectory($pwd);
    
    return $isDir;
  }

  public function isFile($path)
  {
    // Meh
    return !$this->isDirectory($path);
  }

  public function getSystemType()
  {
    if( null === $this->_systemType ) {
      $systype = @ftp_systype($this->getResource());
      $this->_systemType = self::processSystemType($systype);
    }
    return $this->_systemType;
  }
  
  public function stat($path)
  {
    $path = $this->path($path);

    // We have to get the info for parent directory -_-
    $stat = null;
    try {
      foreach( $this->listAndParse(dirname($path)) as $child ) {
        if( $child['name'] == basename($path) ) {
          $stat = $child;
          break 1;
        }
      }
    } catch( Exception $e ) {
      $stat = null;
    }

    return $this->_formatStat($stat);
  }



  // General

  public function copy($sourcePath, $destPath)
  {
    $sourcePath = $this->path($sourcePath);
    $destPath = $this->path($destPath);

    $tmpFile = tempnam('/tmp', 'engine_vfs') . basename($sourcePath);

    try {
      $this->get($tmpFile, $sourcePath);
      $this->put($destPath, $tmpFile);

      // Set umask permission
      // @todo this should probably actually copy the src perms
      try {
        $this->mode($directory, $this->getUmask(0777));
      } catch( Exception $e ) {
        // Silence
      }
      
      $return = true;
    } catch( Exception $e ) {
      $return = false;
    }
    
    @unlink($tmpFile);
    
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to copy "%s" to "%s"', $sourcePath, $destPath));
    }

    return true;
  }

  public function get($local, $path)
  {
    $path = $this->path($path);

    // Get mode
    $mode = $this->getFileMode($path);

    // Non-blocking mode
    if( @function_exists('ftp_nb_get') ) {
      $resource = $this->getResource();
      $res = @ftp_nb_get($resource, $local, $path, $mode);
      while( $res == FTP_MOREDATA ) {
        //$this->_announce('nb_get');
        $res = @ftp_nb_continue($resource);
      }
      $return = ( $res === FTP_FINISHED );
    }
    // Blocking mode
    else {
      $return = @ftp_get($this->_handle, $local, $path, $mode);
    }

    // Error
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get "%s" to "%s"', $path, $local));
    }

    return true;
  }

  public function getContents($path)
  {
    $path = $this->path($path);

    // Create stack buffer
    Engine_Stream_Stack::registerWrapper();
    $stack = fopen('stack://tmp');
    if( !$stack ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to create stack buffer'));
    }

    // Get mode
    $mode = $this->getFileMode($path);

    // Non-blocking mode
    if( @function_exists('ftp_nb_fget') ) {
      $resource = $this->getResource();
      $res = @ftp_nb_fget($resource, $stack, $path, $mode);
      while( $res == FTP_MOREDATA ) {
        //$this->_announce('nb_get');
        $res = @ftp_nb_continue($resource);
      }
      $return = ( $res === FTP_FINISHED );
    }
    // Blocking mode
    else {
      $return = @ftp_fget($this->_handle, $stack, $path, $mode);
    }

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get contents of "%s"', $path));
    }

    $data = '';
    while( false != ($dat = fread($stack, 1024)) ) {
      $data .= $dat;
    }
    
    return $data;
  }

  public function mode($path, $mode, $recursive = false)
  {
    $path = $this->path($path);

    $return = @ftp_chmod($this->getResource(), self::processMode($mode), $path);

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
    
    return true;
  }

  public function move($oldPath, $newPath)
  {
    $oldPath = $this->path($oldPath);
    $newPath = $this->path($newPath);

    $return = @ftp_rename($this->getResource(), $oldPath, $newPath);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to rename "%s" to "%s"', $oldPath, $newPath));
    }

    return true;
  }

  public function put($path, $local)
  {
    $path = $this->path($path);

    // Directory support
    if( is_dir($local) ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put "%s" to "%s": directories not supported', $path, $local));
    }

    // Make sure parent exists
    if( !$this->exists(dirname($path)) ) {
      $this->makeDirectory(dirname($path), true);
    }
    
    // Get mode
    $mode = $this->getFileMode($path);

    // Non-blocking mode
    if( @function_exists('ftp_nb_put') ) {
      $resource = $this->getResource();
      $res = @ftp_nb_put($resource, $path, $local, $mode);
      while( $res == FTP_MOREDATA ) {
        //$this->_announce('nb_put');
        $res = @ftp_nb_continue($resource);
      }
      $return = ( $res === FTP_FINISHED );
    }
    // Blocking mode
    else {
      $return = @ftp_put($this->_handle, $path, $local, $mode);
    }

    // Set umask permission
    try {
      $this->mode($path, $this->getUmask(0666));
    } catch( Exception $e ) {
      // Silence
    }

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put "%s" to "%s"', $path, $local));
    }

    return true;
  }

  public function putContents($path, $data)
  {
    $path = $this->path($path);

    // Create stack buffer
    Engine_Stream_Stack::registerWrapper();
    $stack = @fopen('stack://tmp', 'w+');
    if( !$stack ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to create stack buffer'));
    }

    // Write into stack
    $len = 0;
    do {
      $tmp = @fwrite($stack, substr($data, $len));
      $len += $tmp;
    } while( strlen($data) > $len && $tmp != 0 );
    
    // Get mode
    $mode = $this->getFileMode($path);

    // Non-blocking mode
    if( @function_exists('ftp_nb_fput') ) {
      $resource = $this->getResource();
      $res = @ftp_nb_fput($resource, $path, $stack, $mode);
      while( $res == FTP_MOREDATA ) {
        //$this->_announce('nb_get');
        $res = @ftp_nb_continue($resource);
      }
      $return = ( $res === FTP_FINISHED );
    }
    // Blocking mode
    else {
      $return = @ftp_fput($this->_handle, $path, $stack, $mode);
    }

    // Set umask permission
    try {
      $this->mode($path, $this->getUmask(0666));
    } catch( Exception $e ) {
      // Silence
    }

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put contents to "%s"', $path));
    }
    
    return true;
  }

  public function unlink($path)
  {
    $path = $this->path($path);

    $return = @ftp_delete($this->getResource(), $path);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to unlink "%s"', $path));
    }

    return true;
  }



  // Directories

  public function changeDirectory($directory)
  {
    $directory = $this->path($directory);

    // Only set if connected, we can just set it on connect/login
    if( is_resource($this->_resource) ) {

      $return = @ftp_chdir($this->getResource(), $directory);

      if( !$return ) {
        throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change directory to "%s"', $directory));
      }

    }
    
    $this->_path = $directory;
    return true;
  }

  public function listDirectory($directory, $details = false)
  {
    $directory = $this->path($directory);

    $children = array();
    foreach( $this->listAndParse($directory) as $child ) {
      if( $child['name'] == '.' || $child['name'] == '..' ) continue;
      $child['path'] = $this->path($directory . $this->_directorySeparator . $child['name']);
      if( $details ) {
        $children[] = $this->_formatStat($child);
      } else {
        $children[] = $child['path']; //$this->path($directory . $this->_directorySeparator . $child);
      }
    }
    
    return $children;
  }

  public function makeDirectory($directory, $recursive = false)
  {
    $directory = $this->path($directory);

    // Check if the directory already exists
    if( $this->isDirectory($directory) ) {
      return true;
    }
    
    // Normal
    if( !$recursive ) {

      $return = @ftp_mkdir($this->getResource(), $directory);

      // Set umask permission
      try {
        $this->mode($directory, $this->getUmask(0777));
      } catch( Exception $e ) {
        // Silence
      }

      if( !$return ) {
        throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to make directory "%s"', $directory));
      }
      
      return $return;
    }

    // Recursive
    else {

      $pPath = '';
      $parts = array_filter(explode($this->getDirectorySeparator(), $directory));
      while( count($parts) > 0 ) {
        $pPath .= $this->getDirectorySeparator() . array_shift($parts);

        // If it doesn't exist, create it
        if( !$this->isDirectory($pPath) ) {
          try {
            $this->makeDirectory($pPath, false);
          } catch( Exception $e ) {
            throw $e;
          }
        }
      }
      
      if( !$this->isDirectory($directory) ) {
        throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to make directory "%s"', $directory));
      }
      
      return true;
    }
  }

  public function printDirectory()
  {
    if( null === $this->_path ) {
      $pwd = @ftp_pwd($this->getResource());
      if( !$pwd ) {
        throw new Engine_Vfs_Adapter_Exception('Unable to get working directory');
      }
      $this->_path = $pwd;
    }
    return $this->_path;
  }

  public function removeDirectory($directory, $recursive = false)
  {
    $directory = $this->path($directory);

    if( $recursive ) {
      foreach( $this->directory($directory) as $child ) {
        if( $child->isDirectory() ) {
          $this->removeDirectory($child->getPath(), true);
        } else {
          $this->unlink($child->getPath());
        }
      }
    }

    // Normal
    $return = @ftp_rmdir($this->getResource(), $directory);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to remove directory "%s"', $directory));
    }
    
    return true;
  }






  // Utility

  public function listAndParse($directory)
  {
    $directory = $this->path($directory);
    
    $directoryListing = @ftp_rawlist($this->getResource(), $directory);

    if( !is_array($directoryListing) ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Could not list directory "%s"', $directory));
    }

    return $this->parseRawList($directoryListing);
  }

  public function parseRawList($directoryListing)
  {
    if( !is_array($directoryListing) ) {
      throw new Engine_Vfs_Adapter_Exception('parseRawList only takes an array');
    }

    // Remove header from raw list
    foreach ($directoryListing as $index => $value ) {
      if( strncmp($value, 'total: ', 7) == 0 && preg_match('/total: \d+/', $value) ) {
        unset($directoryListing[$index]);
        break;
      }
    }
    
    // Handle empty directories
    if( count($directoryListing) == 0 ) {
      return array();
    }

    // Exception for some FTP servers seem to return this wiered result instead
    // of an empty list
    if( count($directoryListing) == 1 && $directoryListing[0] == 'total 0') {
      return array();
    }

    // Determine matcher
    if( !isset($this->_lsMatcher) ) {
      $this->_lsMatcher = $this->_determineOsMatch($directoryListing);
    }

    // Process
    $contents = array();
    foreach( $directoryListing as $entry ) {
      if( !preg_match($this->_lsMatcher['pattern'], $entry, $m) ) {
        continue;
      }
      $entry = array();
      foreach( $this->_lsMatcher['map'] as $key => $val ) {
        $entry[$key] = $m[$val];
      }
      $entry['stamp'] = $this->_parseDate($entry['date']);

      $contents[] = $entry;
    }
    
    return $contents;
  }

  protected function _determineOsMatch($directoryListing)
  {
    foreach( $directoryListing as $entry ) {
      foreach( $this->_lsPatterns as $os => $match) {
        if( preg_match($match['pattern'], $entry) ) {
          return $match;
        }
      }
    }

    throw new Engine_Vfs_Adapter_Exception('Unable to determine rawlist regex');
  }

  protected function _parseDate($date)
  {
      // Sep 10 22:06 => Sep 10, <year> 22:06
      if (preg_match('/([A-Za-z]+)[ ]+([0-9]+)[ ]+([0-9]+):([0-9]+)/', $date,
                     $res)) {
          $year    = date('Y');
          $month   = $res[1];
          $day     = $res[2];
          $hour    = $res[3];
          $minute  = $res[4];
          $date    = "$month $day, $year $hour:$minute";
          $tmpDate = strtotime($date);
          if ($tmpDate > time()) {
              $year--;
              $date = "$month $day, $year $hour:$minute";
          }
      } elseif (preg_match('/^\d\d-\d\d-\d\d/', $date)) {
          // 09-10-04 => 09/10/04
          $date = str_replace('-', '/', $date);
      }
      $res = strtotime($date);
      if (!$res) {
        return false; // throw?
      }
      return $res;
  }
  
  public function getUid()
  {
    if( null === $this->_uid ) {
      $info = $this->_getPermTestFile();
      if( $info ) {
        $info = $info->getInfo();
      }
      if( !empty($info['uid']) ) {
        $this->_uid = $info['uid'];
      } else {
        $this->_uid = false;
      }
    }
    return $this->_uid;
  }

  public function getGid()
  {
    if( null === $this->_gid ) {
      $info = $this->_getPermTestFile();
      if( $info ) {
        $info = $info->getInfo();
      }
      if( !empty($info['gid']) ) {
        $this->_gid = $info['gid'];
      } else {
        $this->_gid = false;
      }
    }
    return $this->_gid;
  }

  protected function _getPermTestFile()
  {
    // Remove test file
    if( $this->exists('ftppermtestfile') ) {
      $this->unlink('ftppermtestfile');
    }

    // Put test file
    $this->putContents('ftppermtestfile', 'null');

    // Get info
    return $this->info('ftppermtestfile');
  }

  protected function _formatStat($stat)
  {
    // Missing
    if( !$stat ) {
      return array(
        'name' => basename(@$stat['path']),
        'path' => @$stat['path'],
        'exists' => false,
      );
    }

    $info = array(
      // Exists
      'name' => basename(@$stat['path']),
      'path' => @$stat['path'],
      'exists' => true,

      // Stat
      'uid' => $stat['user'],
      'gid' => $stat['group'],
      'size' => $stat['size'],
      'atime' => null, //$stat['atime'],
      'mtime' => null, //$stat['mtime'],
      'ctime' => null, //$stat['ctime'],

      // Extra
      'rights' => $stat['rights'],
      'type' => ( $stat['is_dir'] == 'd' ? 'dir' : 'file' ),

      // Perms
      //'readable' => $this->checkPerms(0x004, $stat['rights'], $stat['user'], $stat['group']),
      //'writable' => $this->checkPerms(0x002, $stat['rights'], $stat['user'], $stat['group']),
      //'readable' => $this->checkPerms(0x001, $stat['rights'], $stat['user'], $stat['group']),
    );

    return $info;
  }
}