<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Storage_Service_Abstract implements Storage_Service_Interface
{
  protected $_identity;
  
  protected $_scheme;

  protected $_schemeClass = 'Storage_Service_Scheme_Dynamic';

  
  // General

  public function __construct(array $config = array())
  {
    if( !empty($config['service_id']) ) {
      $this->_identity = $config['service_id'];
    }
  }

  public function getIdentity()
  {
    return $this->_identity;
  }


  // Scheme
  
  public function getScheme()
  {
    if( null === $this->_scheme )
    {
      $class = $this->_schemeClass;
      $this->_scheme = new $class();
    }

    return $this->_scheme;
  }

  public function setScheme(Storage_Service_Scheme_Interface $scheme)
  {
    $this->_scheme = $scheme;
    return $this;
  }



  
  /* Utility */
  
  public function getBaseUrl()
  {
    return $this->_removeScriptName(Zend_Controller_Front::getInstance()->getBaseUrl());
  }

  public function fileInfo($file)
  {
    // $file is an instance of Zend_Form_Element_File
    if( $file instanceof Zend_Form_Element_File ) {
      $info = $file->getFileInfo();
      $info = current($info);
    }

    // $file is a key of $_FILES
    else if( is_array($file) ) {
      $info = $file;
    }

    // $file is a string
    else if( is_string($file) ) {
      $info = array(
        'tmp_name' => $file,
        'name' => basename($file),
        'type' => 'unknown/unknown', // @todo
        'size' => filesize($file)
      );

      // Try to get image info  mime_content_type(
      if( function_exists('mime_content_type') && ($mime = mime_content_type($file)) ) {
        $info['type'] = $mime;
      }
    }

    // $file is an unknown type
    else {
      throw new Storage_Service_Exception('Unknown file type specified');
    }

    // Check to make sure file exists and not security problem
    self::_checkFile($info['tmp_name'], 04); // Check for read

    // Do some other stuff
    $mime_parts = explode('/', $info['type'], 2);
    $info['mime_major'] = $mime_parts[0];
    $info['mime_minor'] = $mime_parts[1];
    $info['hash'] = md5_file($info['tmp_name']);
    $info['extension'] = ltrim(strrchr($info['name'], '.'), '.');
    unset($info['type']);
    
    return $info;
  }

  protected function _removeScriptName($url)
  {
    if (!isset($_SERVER['SCRIPT_NAME'])) {
      // We can't do much now can we? (Well, we could parse out by ".")
      return $url;
    }

    if (($pos = strripos($url, basename($_SERVER['SCRIPT_NAME']))) !== false) {
      $url = substr($url, 0, $pos);
    }

    return $url;
  }

  protected function _checkFile($file, $mode = 06)
  {
    // @todo This is fubared, fix up later
    //if( preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $file) )
    //if( preg_match('/[^a-z0-9 \\/\\\\_.:-]/i', $file) )
    //{
      //throw new Storage_Service_Exception(sprintf('Security check: Illegal character in filename: %s', $file));
    //}

    if( $mode && !file_exists($file) )
    {
      throw new Storage_Service_Exception('File does not exist: '.$file);
    }
    
    if( ($mode & 04) && (!is_readable($file)) )
    {
      throw new Storage_Service_Exception('File not readable: '.$file);
    }

    if( ($mode & 02) && (!is_writable($file)) )
    {
      throw new Storage_Service_Exception('File not writeable: '.$file);
    }

    if( ($mode & 01) && (!is_executable($file)) )
    {
      throw new Storage_Service_Exception('File not executable: '.$file);
    }
  }
  
  protected function _mkdir($path, $mode = 0777)
  {
    // Change umask
    if( function_exists('umask') ) {
      $oldUmask = umask();
      umask(0);
    }

    // Change perms
    $code = 0;
    if( is_dir($path) ) {
      @chmod($path, $mode);
    } else if( !@mkdir($path, $mode, true) ) {
      $code = 1;
    }

    // Revert umask
    if( function_exists('umask') ) {
      umask($oldUmask);
    }

    // Respond
    if( 1 == $code ) {
      throw new Storage_Service_Exception(sprintf('Could not create folder: %s', $path));
    }
  }
  
  protected function _move($from, $to)
  {
    // Change umask
    if( function_exists('umask') ) {
      $oldUmask = umask();
      umask(0);
    }

    // Move
    $code = 0;
    if( !is_file($from) ) {
      $code = 1;
    } else if( !@rename($from, $to) ) {
      @mkdir(dirname($to), 0777, true);
      if( !@rename($from, $to) ) {
        $code = 1;
      }
    }

    // Revert umask
    if( function_exists('umask') ) {
      umask($oldUmask);
    }

    if( 1 == $code ) {
      throw new Storage_Service_Exception('Unable to move file ('.$from.') -> ('.$to.')');
    }
  }

  protected function _delete($file)
  {
    // Delete
    $code = 0;
    if( is_file($file) ) {
      if( !@unlink($file) ) {
        @chmod($file, 0777);
        if( !@unlink($file) ) {
          $code = 1;
        }
      }
    }

    if( 1 == $code ) {
      throw new Storage_Service_Exception('Unable to delete file: '.$file);
    }
  }

  protected function _copy($from, $to)
  {
    // Change umask
    if( function_exists('umask') ) {
      $oldUmask = umask();
      umask(0);
    }

    // Copy
    $code = 0;
    if( !is_file($from) ) {
      $code = 1;
    } else if( !@copy($from, $to) ) {
      @mkdir(dirname($to), 0777, true);
      @chmod(dirname($to), 0777);
      if( !@copy($from, $to) ) {
        $code = 1;
      }
    }

    // Revert umask
    if( function_exists('umask') ) {
      umask($oldUmask);
    }

    if( 1 == $code ) {
      throw new Storage_Service_Exception('Unable to copy file ('.$from.') -> ('.$to.')');
    }
  }

  protected function _write($file, $data)
  {
    // Change umask
    if( function_exists('umask') ) {
      $oldUmask = umask();
      umask(0);
    }

    // Write
    $code = 0;
    if( !@file_put_contents($file, $data) ) {
      if( is_file($file) ) {
        @chmod($file, 0666);
      } else if( is_dir(dirname($file)) ) {
        @chmod(dirname($file), 0777);
      } else {
        @mkdir(dirname($file), 0777, true);
      }

      if( !@file_put_contents($file, $data) ) {
        $code = 1;
      }
    }

    // Revert umask
    if( function_exists('umask') ) {
      umask($oldUmask);
    }
    
    if( 1 == $code ) {
      throw new Storage_Service_Exception(sprintf('Unable to write to file: $s', $file));
    }
  }

  protected function _read($file)
  {
    if( !@file_get_contents($file) ) {
      throw new Storage_Service_Exception('Unable to read file: '.$file);
    }
  }
}
