<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FilePermission.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_FilePermission extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'insufficientPermissions' => 'Insufficient permissions',
    'noFile' => 'File does not exist',
    'cannotCheck' => 'Cannot check permissions, SplFileInfo not defined.',
  );

  protected $_messageVariables = array(
    'path' => '_path',
    'value' => '_value',
  );

  protected $_path;

  protected $_basePath;

  protected $_value;

  protected $_recursive = false;

  protected $_ignoreFiles = false;

  protected $_fileUmask = 0x001;

  protected $_ignoreMissing = false;

  protected $_checkParentIfMissing = false;

  //protected $_isWin = false;

  public function getPath()
  {
    return $this->_path;
  }

  public function setPath($path)
  {
    $this->_path = $path;
    return $this;
  }

  public function getBasePath()
  {
    if( defined('APPLICATION_PATH') ) {
      $this->_basePath = APPLICATION_PATH;
    } else {
      $this->_basePath = rtrim(getcwd());
    }
    return $this->_basePath;
  }

  public function setBasePath($basePath)
  {
    $this->_basePath = $basePath;
    return $this;
  }

  public function setValue($value)
  {
    $this->_value = $value;
    return $this;
  }
  
  public function setRecursive($flag)
  {
    $this->_recursive = (bool) $flag;
    return $this;
  }

  public function setIgnoreFiles($flag)
  {
    $this->_ignoreFiles = true;
    return $this;
  }
  
  public function setFileUmask($fileUmask)
  {
    $this->_fileUmask = (int) $fileUmask;
    return $this;
  }

  public function setIgnoreMissing($flag)
  {
    $this->_ignoreMissing = (bool) $flag;
    return $this;
  }

  public function setCheckParentIfMissing($flag)
  {
    $this->_checkParentIfMissing = (bool) $flag;
    return $this;
  }

  public function execute()
  {
    $path = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->getPath();
    $value = $this->_value;

    //$this->_isWin = ( strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN' );

    if( !empty($path) && !empty($value) ) {

      clearstatcache();

      // Whoops file doesn't exist
      if( !file_exists($path) ) {
        if( $this->_ignoreMissing ) {
          return;
        } else if( $this->_checkParentIfMissing ) {
          $last = $parentPath = $path;
          do {
            $path = dirname($parentPath);
            if( is_dir($parentPath) ) {
              $last = null;
            } else {
              $last = $parentPath;
            }
          } while( $parentPath && $last && $last != $parentPath );
          if( !$last && $parentPath ) {
            if( !$this->_checkPerms($parentPath, $value) ) {
              return $this->_error('insufficientPermissions');
            } else {
              return;
            }
          } else {
            return $this->_error('cannotCheck');
          }
        } else {
          return $this->_error('noFile');
        }
      }

      // Check for splfileinfo
      if( !class_exists('SplFileInfo', false) ) {
        return $this->_error('cannotCheck');
      }

      // Check perms (handles recursion)
      if( is_dir($path) ) {
        $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
      }
      if( !$this->_checkPerms($path, $value) ) {
        return $this->_error('insufficientPermissions');
      }
    }
  }

  protected function _checkPerms($path, $value)
  {
    if( !($path instanceof SplFileInfo) ) {
      try {
        $path = new SplFileInfo($path);
      } catch( Exception $e ) {
        return false;
      }
    }

    // Get perms
    $perms = 0;
    if( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || $path->isExecutable() ) $perms |= 0x0001;
    if( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ) $value |= 0x0001;
    if( $path->isWritable()   ) $perms |= 0x0002;
    if( $path->isReadable()   ) $perms |= 0x0004;

    // Apply file umask to requested permission
    if( $path->isFile() ) {
      $value &= ~$this->_fileUmask;
    }
    
    // Check
    if( ($perms & $value) != $value ) {
      return false;
    }
    
    // Recurse if necessary, and is directory
    if( $this->_recursive && $path->isDir() ) {
      try {
        $it = new DirectoryIterator($path->getPathname());
      } catch( Exception $e ) { // AFAIK this is caused by not having enough permissions
        return false;
      }
      foreach( $it as $fileinfo ) {
        $flname = $fileinfo->getFilename();
        if( $fileinfo->isDot() || $flname[0] == '.' || $flname == 'CVS' ) continue;
        if( $this->_ignoreFiles && $fileinfo->isFile() ) continue;

        if( !$this->_checkPerms($fileinfo, $value) ) {
          return false;
        }
      }
    }

    return true;
  }
  
  protected function _alt_stat($file)
  {
    clearstatcache();
    $ss=@stat($file);
    if(!$ss) return false; //Couldnt stat file

    $ts = array(
      0140000=>'ssocket',
      0120000=>'llink',
      0100000=>'-file',
      0060000=>'bblock',
      0040000=>'ddir',
      0020000=>'cchar',
      0010000=>'pfifo'
    );

    $p=$ss['mode'];
    $t=decoct($ss['mode'] & 0170000); // File Encoding Bit

    $str  = (array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
    $str .= (($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
    $str .= (($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
    $str .= (($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
    $str .= (($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
    $str .= (($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
    $str .= (($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));

    $s = array(
      'perms' => array(
        'umask'=>sprintf("%04o",@umask()),
        'human'=>$str,
        'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
        'octal2'=>sprintf("0%o", 0777 & $p),
        'decimal'=>sprintf("%04o", $p),
        'fileperms'=>@fileperms($file),
        'mode1'=>$p,
        'mode2'=>$ss['mode']
      ),
      'owner' => array(
        'fileowner'=>$ss['uid'],
        'filegroup'=>$ss['gid'],
        'owner'=>(function_exists('posix_getpwuid'))?@posix_getpwuid($ss['uid']):'',
        'group'=>(function_exists('posix_getgrgid'))?@posix_getgrgid($ss['gid']):''
      ),
      'file' => array(
        'filename'=>$file,
        'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
        'dirname'=>@dirname($file),
        'basename'=>@basename($file)
      ),
      'filetype' => array(
        'type'=>substr($ts[octdec($t)],1),
        'type_octal'=>sprintf("%07o", octdec($t)),
        'is_file'=>@is_file($file),
        'is_dir'=>@is_dir($file),
        'is_link'=>@is_link($file),
        'is_readable'=> @is_readable($file),
        'is_writable'=> @is_writable($file)
      ),
      'device' => array(
        'device'=>$ss['dev'], //Device
        'device_number'=>$ss['rdev'], //Device number, if device.
        'inode'=>$ss['ino'], //File serial number
        'link_count'=>$ss['nlink'], //link count
        'link_to'=>($s['type']=='link') ? @readlink($file) : ''
      ),
      'size' =>array(
        'size'=>$ss['size'], //Size of file, in bytes.
        'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
        'block_size'=> $ss['blksize'] //Optimal block size for I/O.
      ),
      'time' => array(
        'mtime'=>$ss['mtime'], //Time of last modification
        'atime'=>$ss['atime'], //Time of last access.
        'ctime'=>$ss['ctime'], //Time of last status change
        'accessed'=>@date('Y M D H:i:s',$ss['atime']),
        'modified'=>@date('Y M D H:i:s',$ss['mtime']),
        'created'=>@date('Y M D H:i:s',$ss['ctime'])
      ),
    );

    clearstatcache();
    return $s;
  }
}