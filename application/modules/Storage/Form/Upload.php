<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Upload.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Upload extends Zend_Form
{
  protected $_mode = 'normal';

  protected $_fileElementName = 'Filedata';

  public function init()
  {
    $this->setAttrib('enctype', 'multipart/form-data');
    $this->getFileElement();
  }

  public function setMode($mode)
  {
    $this->_mode = $mode;
    return $this;
  }

  public function getMode()
  {
    return $this->_mode;
  }

  public function getFileElement()
  {
    $element = $this->getElement($this->_fileElementName);
    if( !$element )
    {
      $element = new Zend_Form_Element_File($this->_fileElementName);
      $element->setDestination(APPLICATION_PATH.'/public/temporary/');
        //->setMultiFile(2)
        //->addValidator('Count', false, 1)
        //->addValidator('Size', false, 102400)
        //->addValidator('Extension', false, 'jpg,png,gif,jpeg');
      $this->addElement($element, $this->_fileElementName);
    }
    return $element;
  }


  /* Input Count */

  protected $_inputCount = 1;

  public function setInputCount($count)
  {
    $this->_inputCount = $count;
    return $this;
  }

  public function getInputCount()
  {
    return $this->_inputCount;
  }


  /* Storage */

  protected $_storage;

  protected $_storageType;

  public function getStorage()
  {
    if( is_null($this->_storage) )
    {
      Engine_Loader::loadClass('File_Model_Storage');
      $this->_storage = File_Model_Storage::getInstance();
    }
    return $this->_storage;
  }

  public function setStorageType($type)
  {
    $this->_storageType = $type;
    return $this;
  }

  public function getStorageType()
  {
    if( is_null($this->_storageType) )
    {
      $this->_storageType = 'local';
    }
    return $this->_storageType;
  }
}