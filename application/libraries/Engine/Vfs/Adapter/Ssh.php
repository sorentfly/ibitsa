<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ssh.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Adapter/Abstract.php';
//require_once 'Engine/Vfs/Adapter/RemoteAbstract.php';
//require_once 'Engine/Vfs/Adapter/Exception.php';
//require_once 'Engine/Vfs/Directory/Standard.php';
//require_once 'Engine/Vfs/Info/Standard.php';
//require_once 'Engine/Vfs/Object/Standard.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Vfs_Adapter_Ssh extends Engine_Vfs_Adapter_RemoteAbstract
{
  protected $_port = 22;
  
  protected $_publicKey;

  protected $_privateKey;

  protected $_hostKey;

  protected $_sftpResource;

  protected $_lastError;



  // General

  public function __construct(array $config = null)
  {
    if( !extension_loaded('ssh2') ) {
      throw new Engine_Vfs_Adapter_Exception('The ssh2 extension is not installed, unable to initialize SSH-VFS');
    }
    parent::__construct($config);
  }
  
  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_privateKey', '_publicKey', '_hostKey'
    ));
  }

  public function getSftpResource()
  {
    if( null === $this->_sftpResource ) {
      $this->_sftpResource = @ssh2_sftp($this->getResource());
      if( null === $this->_sftpResource ) {
        throw new Engine_Vfs_Adapter_Exception('Unable to get sftp resource');
      }
    }
    return $this->_sftpResource;
  }
  
  public function setPublicKey($publicKey)
  {
    $this->_publicKey = $publicKey;
    return $this;
  }

  public function getPublicKey()
  {
    return $this->_publicKey;
  }

  public function setPrivateKey($privateKey)
  {
    $this->_privateKey = $privateKey;
    return $this;
  }

  public function getPrivateKey()
  {
    return $this->_privateKey;
  }

  public function setHostKey($hostKey)
  {
    $this->_hostKey = $hostKey;
    return $this;
  }

  public function getHostKey()
  {
    if( null === $this->_hostKey ) {
      $this->_hostKey = 'ssh-rsa';
    }
    return $this->_hostKey;
  }



  // Connection

  public function connect()
  {
    $publicKey = $this->getPublicKey();
    $privateKey = $this->getPrivateKey();
    $hostKey = $this->getHostKey();

    // Connect with keys
    if( ($publicKey && $privateKey && $hostKey) ) {
      $resource = @ssh2_connect($this->getHost(), $this->getPort(), array(
        'hostkey' => $this->getHostKey(),
      ), array(
        'disconnect' => array($this, 'onDisconnect'),
      ));
    }

    // Connect without keys
    else {
      $resource = @ssh2_connect($this->getHost(), $this->getPort(), array(
        
      ), array(
        'disconnect' => array($this, 'onDisconnect'),
      ));
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
      // @todo do something with the output
      $return = $this->command('exit')
        // Meh
        || true;

      //$return = fclose($this->getResource());
      if( !$return ) {
        throw new Engine_Vfs_Adapter_Exception('Disconnect failed.');
      }
      $this->_resource = null;
    }

    return $this;
  }

  public function login()
  {
    $username = $this->getUsername();
    $password = $this->getPassword();
    $publicKey = $this->getPublicKey();
    $privateKey = $this->getPrivateKey();
    $hostKey = $this->getHostKey();

    // Auth using keys
    if( $publicKey && $privateKey && $hostKey ) {
      $return = @ssh2_auth_pubkey_file($this->getResource(), $username, $publicKey, $privateKey, $password);
    }

    // Auth using username/password only
    else if( $username && $password ) {
      $return = @ssh2_auth_password($this->getResource(), $username, $password);
    }

    // Auth using none
    else {
      $return = @ssh2_auth_none($this->getResource(), $username);
    }

    // Failure
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception('Login failed.');
    }

    return $this;
  }

  public function command($command)
  {
    $stream = @ssh2_exec($this->getResource(), $command);
    if( !$stream ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to execute command "%s"', $command));
    }
    $errorStream = @ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

    stream_set_blocking($stream, true);
    stream_set_timeout($stream, $this->getTimeout());
    if( $errorStream ) {
      stream_set_blocking($errorStream, true);
      stream_set_timeout($errorStream, $this->getTimeout());
    }

    $data = stream_get_contents($stream);
    $error = '';
    if( $errorStream ) {
      $error = stream_get_contents($errorStream);
    }
    
    fclose($stream);
    if( $errorStream ) {
      fclose($errorStream);
    }
    
    $this->_lastError = $error;

    return trim($data);
    /*
    if( is_bool($data) ) {
      return $data;
    } else if( '' == ($data = trim($data)) ) {
      return false;
    } else {
      return $data;
    }
     * 
     */
  }



  // Events

  public function onDisconnect()
  {
    // @todo more fun stuff
    throw new Engine_Vfs_Adapter_Exception('Disconnected from server');
  }



  // Informational

  public function exists($path)
  {
    $path = $this->path($path);

    return file_exists('ssh2.sftp://' . $this->getSftpResource() . $path);
  }

  public function isDirectory($path)
  {
    $path = $this->path($path);

    return is_dir('ssh2.sftp://' . $this->getSftpResource() . $path);
  }

  public function isFile($path)
  {
    $path = $this->path($path);

    return is_file('ssh2.sftp://' . $this->getSftpResource() . $path);
  }

  public function getSystemType()
  {
    if( null === $this->_systemType ) {
      if( substr($this->printDirectory(), 1, 2) == ':\\' ) {
        $this->_systemType = self::SYS_WIN;
      } else {
        $systype = $this->command('uname');
        if( !$systype ) {
          // Shall we throw or just return linux (since it's not windows at least)
          throw new Engine_Vfs_Adapter_Exception(sprintf('Unknown remote system type'));
          //return self::SYS_LIN;
        }
        $this->_systemType = self::processSystemType($systype);
      }
    }
    return $this->_systemType;
  }
  
  public function stat($path)
  {
    $path = $this->path($path);
    $stat = @ssh2_sftp_stat($this->getSftpResource(), $path);

    // Missing
    if( !$stat ) {
      return array(
        'name' => basename($path),
        'path' => $path,
        'exists' => false,
      );
    }

    // Get extra
    $type = filetype('ssh2.sftp://' . $this->getSftpResource() . $path);
    $rights = substr(sprintf('%o', fileperms('ssh2.sftp://' . $this->getSftpResource() . $path)), -4);

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
      'atime' => ( isset($stat['atime']) ? $stat['atime'] : null ),
      'mtime' => ( isset($stat['mtime']) ? $stat['mtime'] : null ),
      'ctime' => ( isset($stat['ctime']) ? $stat['ctime'] : null ),
      
      // Perms
      'rights' => $rights,
      'readable' => $this->checkPerms(0x004, $rights, $stat['uid'], $stat['gid']),
      'writable' => $this->checkPerms(0x002, $rights, $stat['uid'], $stat['gid']),
      'executable' => $this->checkPerms(0x001, $rights, $stat['uid'], $stat['gid']),
      //'readable' => is_readable($path),
      //'writable' => is_writable($path),
      //'executable' => is_executable($path),
    );

    return $info;
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
      try {
        $this->mode($destPath, $this->getUmask(0666));
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

    // @todo implement nb?
    $return = @ssh2_scp_recv($this->getResource(), $path, $local);
    
    // Error
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get "%s" to "%s"', $path, $local));
    }

    return true;
  }

  public function getContents($path)
  {
    $path = $this->path($path);

    $contents = file_get_contents('ssh2.sftp://' . $this->getSftpResource() . $path);

    if( !$contents ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to get contents of "%s"', $path));
    }

    return $contents;
  }

  public function mode($path, $mode, $recursive = false)
  {
    $path = $this->path($path);

    if( !$this->exists($path) ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change mode on "%s"; it does not exist', $path));
    }

    $return = $this->command(sprintf('chmod ' . ($recursive ? '-R ' : ''). ' %o %s', self::processMode($mode), escapeshellarg($path)));
    if( '' != $return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change mode on "%s" - %s', $path, $return));
    }

    return true;
  }

  public function move($oldPath, $newPath)
  {
    $oldPath = $this->path($oldPath);
    $newPath = $this->path($newPath);

    $return = @ssh2_sftp_rename($this->getSftpResource(), $oldPath, $newPath);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to rename "%s" to "%s"', $oldPath, $newPath));
    }

    return true;
  }

  public function put($path, $local)
  {
    $path = $this->path($path);

    // @todo implement nb?
    $return = @ssh2_scp_send($this->getResource(), $local, $path, $this->getUmask(0666));
    
    // Error
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to put "%s" to "%s"', $local, $path));
    }
    
    return true;
  }

  public function putContents($path, $data)
  {
    $path = $this->path($path);

    $return = file_put_contents('ssh2.sftp://' . $this->getSftpResource() . $path, $data);

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

    $return = @ssh2_sftp_unlink($this->getSftpResource(), $path);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to unlink "%s"', $path));
    }

    return true;
  }



  // Directories

  public function changeDirectory($directory)
  {
    $directory = $this->path($directory);
    
    if( !$this->isDirectory($directory) ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change directory to "%s", target is not a directory', $directory));
    }

    // Note: this is totally not working
    $ret = $this->command('cd ' . $directory);
    
    /*
    $curDir = $this->command('pwd');

    // Check against specified directory
    if( $directory != $curDir ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change directory to "%s", new directory did not match (%s)', $directory, $curDir));
    }


    if( !$return && false ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to change directory to "%s"', $directory));
    }
    */
    
    $this->_path = $directory;
    return true;
  }

  public function listDirectory($directory, $details = false)
  {
    $directory = $this->path($directory);

    $children = array();
    foreach( scandir('ssh2.sftp://' . $this->getSftpResource() . $directory) as $child ) {
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

    if( $this->isDirectory($directory) ) {
      return true;
    }

    $return = @ssh2_sftp_mkdir($this->getSftpResource(), $directory, $this->getUmask(0777), $recursive);
    
    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to make directory "%s"', $directory));
    }

    return $return;
  }

  public function printDirectory()
  {
    if( null === $this->_path ) {
      $pwd = $this->command('pwd');
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
    $return = @ssh2_sftp_rmdir($this->getSftpResource(), $directory);

    if( !$return ) {
      throw new Engine_Vfs_Adapter_Exception(sprintf('Unable to remove directory "%s"', $directory));
    }

    return true;
  }



  // Utility

  public function getUid()
  {
    if( null === $this->_uid ) {
      $ret = $this->command('echo $UID');
      if( $ret === '0' ) {
        $this->_uid = 0;
      } else if( !$ret || $ret == '$UID' ) {
        $this->_uid = false;
      } else {
        $this->_uid = (int) $ret;
        // Cannot be zero
        if( $this->_uid == 0 ) {
          $this->_uid = false;
        }
      }
    }
    return $this->_uid;
  }

  public function getGid()
  {
    if( null === $this->_gid ) {
      $ret = $this->command('echo $GROUPS');
      if( $ret === '0' ) {
        $this->_gid = 0;
      } else if( !$ret || $ret == '$GROUPS' ) {
        $this->_gid = false;
      } else {
        $this->_gid = (int) $ret;
        // Cannot be zero
        if( $this->_gid == 0 ) {
          $this->_gid = false;
        }
      }
    }
    return $this->_gid;
  }
}