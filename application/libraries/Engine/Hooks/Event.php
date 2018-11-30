<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Hooks
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Event.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Hooks
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Hooks_Event implements Iterator
{
  // Properties

  /**
   * The name of this event
   * 
   * @var string
   */
  protected $_name;

  /**
   * Continue calling registered hooks?
   * 
   * @var string
   */
  protected $_propogate = true;
  
  /**
   * The data payload
   * 
   * @var mixed
   */
  protected $_payload;
  
  /**
   * The response
   * 
   * @var array
   */
  protected $_response;

  /**
   * For Iterator interface
   * 
   * @var integer
   */
  private $_position = 0;

  
  // General

  /**
   * Constructor
   * 
   * @param string $name The event name
   * @param mixed $payload The event data
   */
  public function __construct($name, $payload = null)
  {
    $this->_position = 0;
    $this->_setName($name);
    $this->setPayload($payload);
  }

  /**
   * Get the name of the event
   * 
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }

  /**
   * Set the name of the event
   * 
   * @param string $name
   */
  protected function _setName($name)
  {
    $this->_name = $name;
  }

  /**
   * Stops further hooks that were registered to the event from being called
   * 
   * @return Engine_Hooks_Event
   */
  public function stopPropogation()
  {
    $this->_propogate = false;
    return $this;
  }

  /**
   * Set the payload
   * 
   * @param mixed $payload
   * @return Engine_Hooks_Event
   */
  public function setPayload($payload)
  {
    $this->_payload = $payload;
    return $this;
  }

  /**
   * Get the payload
   * 
   * @return mixed
   */
  public function getPayload()
  {
    return $this->_payload;
  }

  /**
   * Set the response
   * 
   * @param mixed $value
   */
  public function setResponse($value)
  {
    $this->_response = array($value);
  }

  /**
   * Add a response
   *
   * @param mixed $value
   * @param string|null $key
   */
  public function addResponse($value, $key = null)
  {
    if( is_null($key) )
    {
      $this->_response[] = $value;
    }
    else
    {
      $this->_response[$key] = $value;
    }
  }

  /**
   * Get the first response
   * 
   * @return mixed
   */
  public function getResponse()
  {
    if( !isset($this->_response[0]) )
    {
      return null;
    }
    return $this->_response[0];
  }

  /**
   * Get all responses
   * 
   * @return array
   */
  public function getResponses()
  {
    return $this->_response;
  }


  // Iterator

  function rewind()
  {
    $this->_position = 0;
  }

  function current()
  {
    return $this->_response[$this->_position];
  }

  function key()
  {
    return $this->_position;
  }

  function next()
  {
    ++$this->_position;
  }

  function valid()
  {
    return isset($this->_response[$this->_position]);
  }
}