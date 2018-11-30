<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ProxyObject
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ProxyObject.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_ProxyObject
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_ProxyObject
{
  /**
   * The object to request calls through.
   *
   * @var object
   */
  protected $_sender;

  /**
   * The object to receive calls
   * 
   * @var object
   */
  protected $_receiver;

  /**
   * Constructor
   * 
   * @param object $sender The object to request calls through.
   * @param object $receiver The object to receive calls
   */
  public function __construct($sender, $receiver)
  {
    if( !is_object($sender) || !is_object($receiver) )
    {
      throw new Engine_Exception('Sender and reciever must both be objects');
    }
    $this->_sender = $sender;
    $this->_receiver = $receiver;
  }

  /**
   * Proxy emulation
   * 
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, array $arguments)
  {
    // Requested method
    if( method_exists($this->_receiver, $method) )
    {
      $r = new ReflectionMethod($this->_receiver, $method);
      array_unshift($arguments, $this->_sender);
      $return = $r->invokeArgs($this->_receiver, $arguments);
      // Hack to make method chaining work
      if( $return === $this->_receiver )
      {
        return $this;
      }
      return $return;
    }

    // __call
    if( method_exists($this->_receiver, '__call') )
    {
      array_unshift($arguments, $this->_sender);
      $return = $this->_receiver->__call($method, $arguments);
      // Hack to make method chaining work
      if( $return === $this->_receiver )
      {
        return $this;
      }
      return $return;
    }

    // Whoops, method doesn't exist
    throw new Engine_Exception(sprintf('ProxyObject method "%s" does not exist and could not be trapped in __call().', $method));
  }

  /**
   * Gets the sender
   *
   * @return object
   */
  public function getSender()
  {
    return $this->_sender;
  }

  /**
   * Gets the receiver
   * 
   * @return object
   */
  public function getReceiver()
  {
    return $this->_receiver;
  }
}