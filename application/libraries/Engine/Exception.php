<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Exception
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Exception
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Exception extends Exception
{
  /**
   * Static logger for exceptions
   * 
   * @var Zend_Log
   */
  static protected $_log;

  static protected $_exitImmediately = false;

  /**
   * Inject logging logic
   * @see Exception
   */
  public function __construct($message = '', $code = 0, Exception $previous = null)
  {
    if( version_compare(PHP_VERSION, '5.3.0') >= 0 ) { // Add previous if we're >= 5.3.0
      parent::__construct((string) $message, (int) $code, $previous);
    } else {
      parent::__construct((string) $message, (int) $code);
    }

    if( null !== self::$_log )
    {
      self::$_log->log($this->__toString(), Zend_Log::WARN);
    }

    if( true === self::$_exitImmediately ) {
      echo $this->__toString();
      die();
    }
  }

  /**
   * Set the exception logger
   * 
   * @param Zend_Log $log
   */
  static public function setLog(Zend_Log $log = null)
  {
    self::$_log = $log;
  }

  /**
   * Get the excetpion logger
   *
   * @return Zend_Log
   */
  static public function getLog()
  {
    return self::$_log;
  }
}
