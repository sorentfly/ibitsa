<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Message.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Message
{
  protected $_code;

  protected $_type;

  protected $_message;

  protected $_values;

  public function __construct($code, $type, $message, $values = array())
  {
    $this->_code = $code;
    $this->_type = $type;
    $this->_message = $message;
    $this->_values = $values;
  }

  public function getCode()
  {
    return $this->_code;
  }

  public function getMessage()
  {
    $message = $this->_message;

    // Translation
    $translate = Engine_Sanity::getDefaultTranslator();
    if( null !== $translate ) {
      $message = $translate->_($message);
    }

    foreach( $this->_values as $key => $value ) {
      if( is_array($value) ) {
        $value = join(', ', $value);
      } else if( is_object($value) && method_exists($value, '__toString') ) {
        $value = $value->__toString();
      }
      $message = str_replace("%$key%", (string) $value, $message);
    }

    return $message;
  }
  
  public function toString()
  {
    return $this->getMessage();
  }
  
  public function __toString()
  {
    return $this->getMessage();
  }
}
