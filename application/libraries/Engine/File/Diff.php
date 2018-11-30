<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Diff.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff
{
  // Constants
  const SHIFT = 5;

  // Common states
  const IDENTICAL = 1;
  const DIFFERENT = 2;
  const ADDED = 4;
  const REMOVED = 8;
  const MISSING = 16;

  // Three-way states
  const IMPOSSIBLE = 32;

  const IGNORE = 33;
  const ADD = 516;
  const REPLACE = 34;
  const REMOVE = 40;

  const DIFFERENT_REMOVED = 72;
  const DIFFERENT_DIFFERENT = 66;
  const ADDED_ADDED = 132;



  // Properties

  protected $_left;

  protected $_right;

  protected $_result;

  static protected $_codes = array(
    self::IDENTICAL             => 'identical',
    self::DIFFERENT             => 'different',
    self::ADDED                 => 'added',
    self::REMOVED               => 'removed',
    self::MISSING               => 'missing',
    self::IMPOSSIBLE            => 'impossible',

    self::IGNORE                => 'ignore',
    self::ADD                   => 'add',
    self::REPLACE               => 'replace',
    self::REMOVE                => 'remove',

    self::DIFFERENT_REMOVED     => 'different_removed',
    self::DIFFERENT_DIFFERENT   => 'different_different',
    self::ADDED_ADDED           => 'added_added',
  );

  static protected $_errors = array(
    // This is an error for two-way
    self::DIFFERENT             => 'different',

    // Three way
    self::IMPOSSIBLE            => 'impossible',
    self::DIFFERENT_REMOVED     => 'different_removed',
    self::DIFFERENT_DIFFERENT   => 'different_different',
    self::ADDED_ADDED           => 'added_added',
  );

  static protected $_messages = array(
    self::DIFFERENT             => 'different',
    self::IMPOSSIBLE            => 'impossible',
    self::DIFFERENT_REMOVED     => 'removed',
    self::DIFFERENT_DIFFERENT   => 'different',
    self::ADDED_ADDED           => 'different',
  );



  // Static

  static public function factory($left, $right, $original = null)
  {
    if( null === $original ) {
      return new Engine_File_Diff($left, $right);
    } else {
      return new Engine_File_Diff3($left, $right, $original);
    }
  }

  static public function getCodeKey($code)
  {
    if( isset(self::$_codes[$code]) ) {
      return self::$_codes[$code];
    } else {
      return null;
    }
  }

  static public function getErrorCodeKey($code)
  {
    if( isset(self::$_errors[$code]) ) {
      return self::$_errors[$code];
    }
    return null;
  }

  static public function getCodeMessage($code)
  {
    if( isset(self::$_messages[$code]) ) {
      return self::$_messages[$code];
    }
    return null;
  }



  // General

  public function __construct($left, $right)
  {
    $this->_left = self::_procFile($left);
    $this->_right = self::_procFile($right);
  }

  public function execute()
  {
    $status = self::compare($this->_left, $this->_right);
    $this->_result = new Engine_File_Diff_Result($status, $this->_left, $this->_right);
    return $this;
  }

  public function getLeft()
  {
    return $this->_left;
  }

  public function getRight()
  {
    return $this->_right;
  }

  public function getResult()
  {
    if( null === $this->_result ) {
      $this->execute();
      if( null === $this->_result ) {
        throw new Engine_File_Diff_Exception('No result set after running execute');
      }
    }
    return $this->_result;
  }

  public function getStatus()
  {
    return $this->_result->getStatus();
  }

  public function getCode()
  {
    return $this->_result->getCode();
  }

  public function getMessage()
  {
    return $this->_result->getMessage();
  }

  public function isError()
  {
    return $this->_result->isError();
  }

  public function toArray()
  {
    return array(
      'isError' => $this->isError(),
      'code' => $this->getCode(),
      //'key' => $this->getKey(),
      'rightPath' => $this->getRight()->getPath(),
      'leftPath' => $this->getLeft()->getPath(),
    );
  }



  // Static

  static public function compare($left, $right)
  {
    // Check args
    $left = self::_procFile($left);
    $right = self::_procFile($right);

    // Compare
    $status = null;
    if( !$left->getExists() && !$right->getExists() ) {
      // Both missing
      $status = self::MISSING;
    } else if( !$left->getExists() ) {
      // Left missing
      $status = self::ADDED;
    } else if( !$right->getExists() ) {
      // Right missing
      $status = self::REMOVED;
    } else if( !$left->getHash() || !$right->getHash() /* || !$left->getSize() || !$right->getSize()*/ ) {
      // All the other information should be filled in after this point
      throw new Engine_File_Diff_Exception(sprintf('Both files exists, however one is missing the size or hash: %s - %s', $left->getPath(), $right->getPath()));
    } else if( $left->getHash() != $right->getHash() ) {
      // Hashes don't match
      $status = self::DIFFERENT;
    } else if(
        ($left->getSize() == 0 && $right->getSize() != 0 ) ||
        ($left->getSize() != 0 && $right->getSize() == 0 ) || (
          $left->getSize() &&
          (abs($left->getSize() - $right->getSize()) / $left->getSize()) > 0.15
        )
      ) {
      // Rather than check if they are identical ($left->getSize() != $right->getSize()),
      // check if they are within ~15% (paranoia, eol fat etc)
      // This should probably be a fixed byte size or a combination eventually
      $status = self::DIFFERENT;
    } else {
      // Identical! YAY!
      $status = self::IDENTICAL;
    }

    return $status;
  }



  // Utility

  static protected function _procFile($file)
  {
    if( !($file instanceof Engine_File_Diff_File) ) {
      $file = new Engine_File_Diff_File($file);
    }
    return $file;
  }

  static protected function _procResult($result)
  {
    if( !($result instanceof Engine_File_Diff_Result) ) {
      throw new Engine_File_Diff_Exception(sprintf('Invalid type given to "%1$s::%2$s": %3$s', __CLASS__, __METHOD__, gettype($result)));
    }
    return $result;
  }

  static protected function _procResultOrFile($result_or_file)
  {
    if( !($result_or_file instanceof Engine_File_Diff_Result) ) {
      if( is_numeric($result_or_file) ) {
        throw new Engine_File_Diff_Exception(sprintf('Invalid type given to "%1$s::%2$s": %3$s', __CLASS__, __METHOD__, gettype($result_or_file)));
      } else {
        $result_or_file = self::_procFile($result_or_file);
      }
    }
    return $result_or_file;
  }
}