<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Directory.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_Directory extends Engine_Package_Manifest_Entity_Abstract
{
  protected $_path;

  protected $_structure;

  protected $_addDirectoryToArchive = true;

  protected $_props = array(
    'type',
    'path',
    'structure',
  );
  
  public function __construct($spec, $options = null)
  {
    if( is_array($spec) ) {
      $this->fromArray($spec);
    }
    if( is_array($options) ) {
      $this->setOptions($options);
    }
    if( is_string($spec) ) {
      $this->read($spec);
    }
  }

  public function getType()
  {
    return 'directory';
  }

  public function getPath()
  {
    if( null === $this->_path ) {
      throw new Engine_Package_Manifest_Exception('Path cannot be empty');
    }
    return $this->_path;
  }

  public function setPath($path)
  {
    $this->_path = $path;
    return $this;
  }

  public function getAddDirectoryToArchive()
  {
    return (bool) $this->_addDirectoryToArchive;
  }

  public function setAddDirectoryToArchive($flag = false)
  {
    $this->_addDirectoryToArchive = (bool) $flag;
    return $this;
  }

  public function getStructure()
  {
    return $this->_structure;
  }

  public function setStructure(array $structure)
  {
    $this->_structure = $structure;
    return $this;
  }
  
  public function getFileStructure($assoc = false)
  {
    $files = array();
    foreach( $this->getStructure() as $struct ) {
      if( isset($struct['path']) ) {
        $struct['path'] = $this->getPath() . '/' . $struct['path'];
      }
      if( $assoc ) {
        $files[$struct['path']] = $struct;
      } else {
        $files[] = $struct['path'];
      }
    }
    return $files;
  }



  // Utility
  
  public function read($directory)
  {
    if( !is_string($directory) ) {
      throw new Engine_Package_Manifest_Exception(sprintf('Directory is not a string, given "%s"', gettype($directory)));
    }
    if( !is_dir($this->getBasePath() . DIRECTORY_SEPARATOR . $directory) ) {
      throw new Engine_Package_Manifest_Exception(sprintf('Directory "%s" is not a directory', $directory));
    }

    $this->_structure = self::build($this->getBasePath() . DIRECTORY_SEPARATOR . $directory);
    $this->setPath($directory);

    return $this;
  }

  public function addToArchive(Archive_Tar $archive)
  {
    if( $this->getAddDirectoryToArchive() ) {
      $rval = $archive->addModify($this->getBasePath() . DIRECTORY_SEPARATOR . $this->getPath(), null, $this->getBasePath());
      if( $archive->isError($rval) ) {
        throw new Engine_Package_Manifest_Exception('Error in archive: ' . $rval->getMessage());
      }
    } else {
      foreach( $this->getStructure() as $key => $value ) {
        $fullpath = rtrim($this->getBasePath(), '/\\') . DIRECTORY_SEPARATOR . $this->getPath() . DIRECTORY_SEPARATOR . $value['path'];
        $fullpath = preg_replace('~[/\\\\]+~', DIRECTORY_SEPARATOR, $fullpath);
        if( is_dir($fullpath) ) continue;
        $rval = $archive->addModify($fullpath, null, $this->getBasePath());
        if( $archive->isError($rval) ) {
          throw new Engine_Package_Manifest_Exception('Error in archive: ' . $rval->getMessage());
        }
      }
    }
  }
}
