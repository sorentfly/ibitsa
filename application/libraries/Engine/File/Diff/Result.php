<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Result.php 10204 2014-05-08 14:09:34Z lucas $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff_Result
{
  protected $_status;

  protected $_left;

  protected $_right;

  protected $_code;

  protected $_isError;
  
  public function __construct($status, $left, $right)
  {
    if( !is_numeric($status) ) {
      throw new Engine_File_Diff_Exception(sprintf('Invalid status type given to "%1$s::%2$s": %3$s', get_class($this), __METHOD__, gettype($status)));
    }
    $this->_status = $status;
    $this->_left = $this->_procFile($left);
    $this->_right = $this->_procFile($right);
  }

  public function getLeft()
  {
    return $this->_left;
  }

  public function getRight()
  {
    return $this->_right;
  }

  public function getCode()
  {
    if( null === $this->_code ) {
      $this->_code = Engine_File_Diff::getCodeKey($this->_status);
      if( null === $this->_code ) {
        $this->_code = false;
      }
    }
    return $this->_code;
  }

  public function getMessage()
  {
    return Engine_File_Diff::getCodeMessage($this->_status);
  }

  public function isError()
  {
    if( null === $this->_isError ) {
      $this->_isError = ( null !== Engine_File_Diff::getErrorCodeKey($this->_status) );
    }
    return $this->_isError;
  }



  // Utility

  protected function _procFile($file)
  {
    if( !($file instanceof Engine_File_Diff_File) ) {
      $file = new Engine_File_Diff_File($file);
    }
    return $file;
  }
}