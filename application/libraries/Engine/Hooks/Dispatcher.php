<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Hooks
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Dispatcher.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Hooks
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Hooks_Dispatcher
{
  // Constants

  const TYPE_CALLBACK = 'callback';
  const TYPE_RESOURCE = 'resource';

  /**
   * Stores the current singleton instance
   *
   * @var Core_Model_Hooks_Dispatcher 
   */
  protected static $_instance;

  /**
   * Array of events that have registered callbacks
   * 
   * @var array
   */
  protected $_events = array();

  /**
   *
   * @var array
   */
  protected $_plugins = array();

  /**
   * Array of events that need sorting
   * 
   * @var array
   */
  protected $_needSorts = array();

  /**
   * Default priority for hooks
   * 
   * @var integer
   */
  protected $_defaultPriority = 500;

  /**
   * Sets when plugins/callbacks are validated.
   *   0 - register
   *   1 - call
   * 
   * @var integer
   */
  protected $_sanityMode = 1;

  /**
   * Gets the current singleton instance
   *
   * @return Engine_Hooks_Dispatcher
   */
  public static function getInstance()
  {
    if( null === self::$_instance )
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Shorthand for {@link Engine_Hooks_Dispatcher::getInstance()}
   * 
   * @return Engine_Hooks_Dispatcher
   */
  public static function _()
  {
    return self::getInstance();
  }

  /**
   * Sets (or if param is not specified, resets) the current singleton instance
   *
   * @param Core_Model_Hooks_Dispatcher $instance - OPTIONAL
   * @return Core_Model_Hooks_Dispatcher
   */
  public static function setInstance(Engine_Hooks_Dispatcher $instance = null)
  {
    if( null === $instance )
    {
      $instance = new self();
    }
    self::$_instance = $instance;
    return self::$_instance;
  }


  
  // Registration

  /**
   * Register a callback/resource to an event
   * 
   * @param string $event The event name
   * @param array $params (OPTIONAL)
   * @return Engine_Hooks_Dispatcher
   */
  public function addEvent($event, $params = null)
  {
    if( null === $params )
    {
      $params = $event;
      $event = @$params['event'];
    }
    if( empty($event) )
    {
      throw new Engine_Hooks_Exception('No event? Really?');
    }
    if( is_object($params) && method_exists($params, 'toArray') )
    {
      $params = $params->toArray();
    }
    if( !is_array($params) )
    {
      throw new Engine_Hooks_Exception('Array or array-type object must be passed to register');
    }
    if( empty($params['callback']) == empty($params['resource']) )
    {
      throw new Engine_Hooks_Exception('Callback or resource must be defined, not neither or both');
    }
    if( !isset($params['priority']) )
    {
      $params['priority'] = $this->_defaultPriority;
    }
    if( !empty($params['resource']) && is_array($params['resource']) )
    {
      $params['resource'] = str_replace(' ', '_', ucwords(join(' ', $params['resource'])));
    }

    $this->_isCallable($params, $event, $this->_sanityMode);

    $name = $this->_getName($params);
    
    $this->_needSorts[$event] = true;
    $this->_events[$event][$name] = $params['priority'];
    $this->_plugins[$name] = $params;
    
    return $this;
  }

  /**
   * Register an array of events. Formats:
   *   event => params, OR
   *   array('event' => event, params)
   * 
   * @param array $events Array of events
   * @return Engine_Hooks_Dispatcher
   */
  public function addEvents($events)
  {
    foreach( $events as $event => $params )
    {
      if( is_numeric($event) )
      {
        $this->addEvent($params);
      }
      else
      {
        $this->addEvent($event, $params);
      }
    }

    return $this;
  }

  /**
   * Trigger an event
   * 
   * @param string $event The event to trigger
   * @param mixed $payload Arbitrary payload data
   * @return Engine_Hooks_Event
   */
  public function callEvent($event, $payload = null)
  {
    // Sort
    $this->_sort($event);

    // Create event object, if necessary
    if( !($payload instanceof Engine_Hooks_Event) )
    {
      $payload = new Engine_Hooks_Event($event, $payload);
    }

    // Ignore if no plugin
    if( empty($this->_events[$event]) )
    {
      return $payload;
    }

    // Send
    foreach( $this->_events[$event] as $name => $priority )
    {
      $params = $this->_plugins[$name];
      $this->_isCallable($params, $event, (bool) $this->_sanityMode);

      if( !empty($params['callback']) )
      {
        call_user_func($params['callback'], $payload);
      }

      else if( !empty($params['resource']) )
      {
        Engine_Api::_()->loadClass($params['resource'])->$event($payload);
      }
    }

    // Return the event object so we can get the responses
    return $payload;
  }

  /**
   * Check if a hook registered to an event is callable
   * 
   * @param array $params
   * @param string $event The event name (need for resources)
   * @param boolean $syntaxOnly Will check syntax only
   */
  protected function _isCallable($params, $event, $syntaxOnly = false)
  {
    if( !empty($params['callback']) )
    {
      if( !is_callable($params['callback'], $syntaxOnly) )
      {
        throw new Engine_Hooks_Exception('Callback is not callable');
      }
    }

    else if( !empty($params['resource']) )
    {
      if( !is_string($params['resource']) )
      {
        throw new Engine_Hooks_Exception('Resource must be a string class name');
      }
      if( !$syntaxOnly && !class_exists($params['resource'] /*, false */) && !method_exists($params['resource'], $event) )
      {
        throw new Engine_Hooks_Exception('Resource is not callable');
      }
    }

    else
    {
      throw new Engine_Hooks_Exception('No callback or resource specified to verify if callable');
    }
  }

  /**
   * Get a unique name based on params
   * 
   * @param array $params
   * @return string
   */
  protected function _getName($params)
  {
    if( !empty($params['callback']) )
    {
      if( is_array($params['callback']) )
      {
        if( is_object($params['callback'][0]) ) {
          return 'a-o' . $this->_objectHash(array_shift($params['callback'])) .
              join('-', $params['callback']);
        } else {
          return 'a-' . join('-', $params['callback']);
        }
      }

      else if( is_scalar($params['callback']) )
      {
        return 's-' . $params['callback'];
      }

      else
      {
        throw new Engine_Hooks_Exception('Callback must be scalar or array');
      }
    }

    else if( !empty($params['resource']) )
    {
      if( is_string($params['resource']) )
      {
        return 'r-' . $params['resource'];
      }

      else
      {
        throw new Engine_Hooks_Exception('Resource must be a string');
      }
    }

    else
    {
      throw new Engine_Hooks_Exception('No callback or resource specified');
    }
  }

  /**
   * Sort an event
   * 
   * @param string $event
   */
  protected function _sort($event)
  {
    if( !empty($this->_needSorts[$event]) )
    {
      asort($this->_events[$event]);
      unset($this->_needSorts[$event]);
    }
  }

  protected function _objectHash($object)
  {
    if( function_exists('spl_object_hash') ) {
      return spl_object_hash($object);
    } else {
      return md5((string)$object);
    }
  }
}

