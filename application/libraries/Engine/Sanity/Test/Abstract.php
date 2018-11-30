<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Sanity_Test_Abstract implements Engine_Sanity_Test_Interface
{
  protected $_type;

  protected $_name;
  
  protected $_messages;

  protected $_emptyMessage = 'OK';
  
  protected $_messageTemplates = array();

  protected $_messageVariables = array();

  protected $_defaultErrorType = Engine_Sanity::ERROR_ERROR;
  
  public function __construct($options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function getType()
  {
    if( null === $this->_type ) {
      $segments = explode('_', get_class($this));
      $this->_type = array_pop($segments);
    }
    return $this->_type;
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value)
    {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function getName()
  {
    if( null === $this->_name ) {
      $translate = Engine_Sanity::getDefaultTranslator();
      if( $translate ) {
        $this->_name = $translate->_(strtolower(get_class($this)) . '_name');
      } else {
        $this->_name = $this->getType();
      }
    }

    $translate = Engine_Sanity::getDefaultTranslator();
    if( $translate ) {
      return $translate->_($this->_name);
    } else {
      return $this->_name;
    }
  }

  public function setDefaultErrorType($type)
  {
    $this->_defaultErrorType = $type;
    return $this;
  }

  public function getDefaultErrorType()
  {
    return $this->_defaultErrorType;
  }

  public function setMessages(array $messages)
  {
    foreach( $messages as $key => $value ) {
      $this->setMessage($key, $value);
    }
    return $this;
  }

  public function setMessage($type, $message)
  {
    if( !isset($this->_messageTemplates[$type]) ) {
      throw new Engine_Package_Exception('Unknown message template type: ' . $type);
    }

    $this->_messageTemplates[$type] = $message;

    return $this;
  }

  public function setEmptyMessage($message)
  {
    $this->_emptyMessage = $message;
    return $this;
  }

  public function getEmptyMessage()
  {
    $translate = Engine_Sanity::getDefaultTranslator();
    if( $translate ) {
      return $translate->_($this->_emptyMessage);
    } else {
      return $this->_emptyMessage;
    }
  }


  // Messages

  public function getMaxErrorLevel()
  {
    $maxErrorLevel = Engine_Sanity::ERROR_NONE;
    foreach( (array) $this->getMessages() as $message ) {
      $maxErrorLevel = max($maxErrorLevel, $message->getCode());
    }
    return $maxErrorLevel;
  }

  public function hasMessages()
  {
    return !empty($this->_messages);
  }

  public function getMessages()
  {
    return $this->_messages;
  }

  public function getMessagesStrings()
  {
    $messages = array();
    foreach( $this->_messages as $key => $value ) {
      $messages[$key] = $value->toString();
    }
    return $messages;
  }



  // Utility

  protected function _error($code = null, $key = null)
  {
    if( !is_numeric($code) && is_string($code) ) {
      $key = $code;
      $code = null;
    }
    if( null === $key ) {
      $keys = array_keys($this->_messageTemplates);
      $key = current($keys);
    }
    if( null === $code ) {
      $code = $this->getDefaultErrorType();
    }
    
    if( !isset($this->_messageTemplates[$key]) ) {
      return;
    }
    
    $message = $this->_messageTemplates[$key];

    $values = array();
    foreach ($this->_messageVariables as $ident => $property) {
      $values[$ident] = $this->$property;
    }
    
    $this->_messages[$key] = new Engine_Sanity_Message($code, $key, $message, $values);
  }
}