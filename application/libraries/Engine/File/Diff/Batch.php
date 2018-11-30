<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Batch.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff_Batch /* extends ArrayObject */
{
  protected $_leftFiles;

  protected $_rightFiles;
  
  protected $_diffs;

  protected $_diffsByCode;

  protected $_attribs;

  protected $_hasError;

  static public function factory(array $leftFiles, array $rightFiles, array $originalFiles = null)
  {
    if( null === $originalFiles ) {
      return new Engine_File_Diff_Batch($leftFiles, $rightFiles);
    } else {
      return new Engine_File_Diff_Batch3($leftFiles, $rightFiles, $originalFiles);
    }
  }
  
  public function __construct(array $leftFiles, array $rightFiles)
  {
    if( count($leftFiles) != count($rightFiles) ) {
      throw new Engine_File_Diff_Exception("Count of left and right did not match");
    }
    $this->_leftFiles = $leftFiles;
    $this->_rightFiles = $rightFiles;
    $this->_diffs = array();
  }

  public function __get($key)
  {
    if( isset($this->_attribs[$key]) ) {
      return $this->_attribs[$key];
    }
    return null;
  }

  public function __set($key, $value)
  {
    $this->_attribs[$key] = $value;
  }

  public function __isset($key)
  {
    return isset($this->_attribs[$key]);
  }

  public function __unset($key)
  {
    unset($this->_attribs[$key]);
  }

  public function execute()
  {
    reset($this->_leftFiles);
    reset($this->_rightFiles);
    while( ($left = current($this->_leftFiles)) && ($right = current($this->_rightFiles)) )
    {
      $diff = Engine_File_Diff::factory($left, $right);
      $diff->execute();
      $this->_diffs[] = $diff;

      next($this->_leftFiles);
      next($this->_rightFiles);
    }
    return $this;
  }

  public function getDiffs()
  {
    return $this->_diffs;
  }

  public function getDiffsByCode()
  {
    if( null === $this->_diffsByCode ) {
      foreach( $this->_diffs as $diff ) {
        $code = $diff->getResult()->getCode();
        $this->_diffsByCode[$code][] = $diff;
      }
    }
    return $this->_diffsByCode;
  }

  public function hasError()
  {
    if( null === $this->_hasError ) {
      $this->_hasError = false;
      foreach( $this->_diffs as $diff ) {
        $this->_hasError |= $diff->isError();
      }
    }
    return $this->_hasError;
  }

  public function toArray()
  {
    $diffs = array();
    //$codeIndex = array();
    foreach( $this->getDiffs() as $diff ) {
      $diffs[] = $diff->toArray();
      //$i = count($diffs);
      //$diffs[$i] = $diff->toArray();
      //$codeIndex[$diff->getCode()][] = $i;
    };
    return array(
      'hasError' => $this->hasError(),
      'diffs' => $diffs,
      //'codeIndex' => $codeIndex,
    );
  }

  protected function _procEach(&$val, $default = false)
  {
    if( !is_array($default) ) {
      return $default;
    }
    @list($key, $value) = $val;

    if( is_array($value) ) {
      return $value;
    } else if( is_string($value) ) {
      return $value;
    } else if( is_string($key) ) {
      return $key;
    } else {
      return $default;
    }
  }
}