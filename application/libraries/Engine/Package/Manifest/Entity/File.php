<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_File extends Engine_Package_Manifest_Entity_Abstract
{
  protected $_path;

  protected $_structure;

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
    return 'file';
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
    if( $assoc ) {
      return array($this->getPath() => $this->getStructure());
    } else {
      return array($this->getPath());
    }
  }



  // Read

  public function read($file)
  {
    if( !is_string($file) ) {
      throw new Engine_Package_Manifest_Exception(sprintf('File is not a string, given "%s"', gettype($file)));
    }
    if( !is_file($this->getBasePath() . DIRECTORY_SEPARATOR . $file) ) {
      throw new Engine_Package_Manifest_Exception(sprintf('File "%s" is not a file', $file));
    }

    $this->_structure = self::build_file($this->getBasePath() . DIRECTORY_SEPARATOR . $file);
    $this->_path = $file;

    return $this;
  }

  public function addToArchive(Archive_Tar $archive)
  {
    $rval = $archive->addModify($this->getBasePath() . DIRECTORY_SEPARATOR . $this->getPath(), null, $this->getBasePath());
    if( $archive->isError($rval) ) {
      throw new Engine_Package_Manifest_Exception('Error in archive: ' . $rval->getMessage());
    }
  }
}
