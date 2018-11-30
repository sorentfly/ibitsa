<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff_File
{
  protected $_path;

  protected $_exists;

  protected $_hash;

  protected $_size;
  
  public function __construct($spec)
  {
    if( $spec instanceof Engine_File_Diff_File ) {
      $spec = $spec->toArray();
    } else if( is_string($spec) ) {
      $spec = self::build($spec);
    } else if( !is_array($spec) ) {
      throw new Engine_File_Diff_Exception(sprintf('Invalid type given to "%1$s::%2$s": %3$s', get_class($this), __METHOD__, gettype($spec)));
    }
    $this->_setOptions($spec);
  }
  
  public function toArray()
  {
    return array(
      'path' => $this->_path,
      'exists' => $this->_exists,
      'hash' => $this->_hash,
      'size' => $this->_size,
    );
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function getExists()
  {
    return (bool) $this->_exists;
  }

  public function getHash()
  {
    return $this->_hash;
  }

  public function getSize()
  {
    return $this->_size;
  }



  // Static

  static public function build($file)
  {
    $exists = file_exists($file);
    return array(
      'path' => $file,
      'exists' => $exists,
      'hash' => ( $exists ? sha1_file($file) : null ),
      'size' => ( $exists ? filesize($file) : null ),
    );
  }

  

  // Utility
  
  protected function _setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = '_set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }
  }

  protected function _setPath($path)
  {
    $this->_path = (string) $path;
  }

  protected function _setExists($exists)
  {
    $this->_exists = (bool) $exists;
  }

  protected function _setHash($hash)
  {
    // @todo regex check maybe
    $this->_hash = (string) $hash;
  }

  protected function _setSize($size)
  {
    $this->_size = (float) $size;
  }
}