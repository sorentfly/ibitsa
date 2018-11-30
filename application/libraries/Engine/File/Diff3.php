<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Diff3.php 10185 2014-04-30 14:46:45Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff3 extends Engine_File_Diff
{
  protected $_original;

  public function __construct($left, $right, $original)
  {
    $this->_original = self::_procFile($original);

    parent::__construct($left, $right);
  }

  public function execute()
  {
    $status = self::compare($this->_left, $this->_right, $this->_original);
    $this->_result = new Engine_File_Diff_Result3($status, $this->_left, $this->_right, $this->_original);
    return $this;
  }

  public function getOriginal()
  {
    return $this->_original;
  }



  // Static

  static public function compare($left, $right, $original = null)
  {
    $left = self::_procResultOrFile($left);
    $right = self::_procResultOrFile($right);

    try {
      $original = self::_procFile($original);
    } catch( Exception $e ) {
      // Silence
    }

    if(
      !($left instanceof Engine_File_Diff_File && $right instanceof Engine_File_Diff_File && $original instanceof Engine_File_Diff_File) &&
      !($left instanceof Engine_File_Diff_Result && $right instanceof Engine_File_Diff_Result && null === $original )
    ) {
      throw new Engine_File_Diff_Exception(sprintf('Engine_File_Diff3::compare must be given two instances of Engine_File_Diff_Result or three instances of Engine_File_Diff_File'));
    }

    if( $left instanceof Engine_File_Diff_File ) {
      $otl_status = parent::compare($original, $left);
      $otr_status = parent::compare($original, $right);
      $lFile = $left;
      $rFile = $right;
      $oFile = $original;
    } else if( $left instanceof Engine_File_Diff_Result ) {
      $otl_status = $left ->getStatus();
      $otr_status = $right->getStatus();
      $lFile = $left->getRight();
      $rFile = $right->getRight();
      $oFile = $left->getLeft();
    } else {
      throw new Engine_File_Diff_Exception("Oh no");
    }

    //          (left_theirs << self::SHIFT) | right_ours
    $combined = ($otl_status << self::SHIFT) | $otr_status;

    switch( $combined ) {
      // Contradicting states
      case ( (self::IDENTICAL << self::SHIFT) | self::ADDED     ):
      case ( (self::IDENTICAL << self::SHIFT) | self::MISSING   ):
      case ( (self::DIFFERENT << self::SHIFT) | self::ADDED     ):
      case ( (self::DIFFERENT << self::SHIFT) | self::MISSING   ):
      case ( (self::ADDED     << self::SHIFT) | self::IDENTICAL ):
      case ( (self::ADDED     << self::SHIFT) | self::DIFFERENT ):
      case ( (self::ADDED     << self::SHIFT) | self::REMOVED   ):
      case ( (self::REMOVED   << self::SHIFT) | self::ADDED     ):
      case ( (self::REMOVED   << self::SHIFT) | self::MISSING   ):
      case ( (self::MISSING   << self::SHIFT) | self::IDENTICAL ):
      case ( (self::MISSING   << self::SHIFT) | self::DIFFERENT ):
      case ( (self::MISSING   << self::SHIFT) | self::REMOVED   ):
        $status = self::IMPOSSIBLE; // ERROR_IMPOSSIBLE
        break;

      // Something went horribly wrong
      default:
        //$status = self::IMPOSSIBLE; // ERROR_IMPOSSIBLE
        throw new Engine_File_Diff_Exception(sprintf('Something went horribly wrong: %s %s %s', gettype($otl_status), gettype($otr_status), gettype($combined)));
        break;


      // Ignore states

      // It's all the same damn thing
      case ( (self::IDENTICAL << self::SHIFT) | self::IDENTICAL ):
      // Theirs changed, but ours didn't
      case ( (self::DIFFERENT << self::SHIFT) | self::IDENTICAL ):
      // They added it, we never had it
      case ( (self::ADDED     << self::SHIFT) | self::MISSING   ):
      // We both removed it
      case ( (self::REMOVED   << self::SHIFT) | self::REMOVED   ):
      // WHERE AM I?! THERES NOTHING HERE!
      case ( (self::MISSING   << self::SHIFT) | self::MISSING   ):
        $status = self::IGNORE;
        break;


      // Add states

      // They removed it, we put it back
      case ( (self::REMOVED   << self::SHIFT) | self::IDENTICAL ):
      // They removed it, we put it back
      case ( (self::REMOVED   << self::SHIFT) | self::DIFFERENT ):
      // They never had it, let's give it to them
      case ( (self::MISSING   << self::SHIFT) | self::ADDED ):
        $status = self::ADD; // ACTION_ADD
        break;


      // Replace states

      // We changed it, update it
      case ( (self::IDENTICAL << self::SHIFT) | self::DIFFERENT ):
        $status = self::REPLACE; // ACTION_REPLACE
        break;


      // Remove states

      // They didn't change it, we removed it
      case ( (self::IDENTICAL << self::SHIFT) | self::REMOVED   ):
        $status = self::REMOVE; // ACTION_REMOVE
        break;


      // Conflict states

      // They changed it, we removed it
      case ( (self::DIFFERENT << self::SHIFT) | self::REMOVED   ):
        $status = self::DIFFERENT_REMOVED; // CONFLICT_REMOVE_MODIFIED
        break;


      // Multi-step states

      // We both changed it, compare
      case ( (self::DIFFERENT << self::SHIFT) | self::DIFFERENT ):
        if( parent::compare($lFile, $rFile) == self::IDENTICAL ) {
          $status = self::IGNORE;
        } else {
          $status = self::DIFFERENT_DIFFERENT;
        }
        break;

      // We both added it, compare
      case ( (self::ADDED     << self::SHIFT) | self::ADDED     ):
        if( parent::compare($lFile, $rFile) == self::IDENTICAL ) {
          $status = self::IGNORE;
        } else {
          $status = self::ADDED_ADDED;
        }
        break;
    }

    return $status;
  }
}