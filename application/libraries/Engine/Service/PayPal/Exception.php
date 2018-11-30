<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_PayPal
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Service_PayPal
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_PayPal_Exception extends Engine_Exception
{
  const UNKNOWN           = 0;

  // General
  const GENERAL           = 1;
  const MISSING_LOGIN     = 2;

  // Request
  const UNKNOWN_PARAM     = 100;
  const MISSING_REQUIRED  = 101;
  const REQ_SUP_CONFLICT  = 102;
  
  // Response

  // Connection
  const HTTP              = 300;
  const IS_EMPTY          = 301;
  const NOT_VALID         = 302;

  static protected $_codeKeys;

  public function __construct($message = '', $code = 'UNKNOWN', Exception $previous = null)
  {
    $keys = self::getCodeKeys();
    if( in_array($code, $keys) ) {
      $code = array_search($code, $keys);
    } else if( isset($keys[$code]) ) {
      // Ok
    } else if( is_numeric($code) ) {
      $code = (int) $code;
    } else {
      $code = 0;
    }

    parent::__construct($message, $code, $previous);
  }
  
  public function getCodeKey()
  {
    $code = (int) $this->getCode();
    $keys = self::getCodeKeys();
    if( isset($keys[$code]) ) {
      return $keys[$code];
    } else {
      return null;
    }
  }

  static public function getCodeKeys()
  {
    if( null === self::$_codeKeys ) {
      $r = new Zend_Reflection_Class(__CLASS__);
      self::$_codeKeys = array_flip($r->getConstants());
    }
    return self::$_codeKeys;
  }
}