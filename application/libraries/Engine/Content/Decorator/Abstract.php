<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Content_Decorator_Abstract
{
  // Constants

  const APPEND  = 'APPEND';
  const PREPEND = 'PREPEND';



  // Properties
  
  protected $_element;
  
  protected $_params = array();

  protected $_placement = 'APPEND';

  protected $_separator = PHP_EOL;


  // General

  public function __construct($options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      } else {
        $this->setParam($key, $value);
      }
    }
    
    return $this;
  }



  // Info

  public function getElement()
  {
    if( null === $this->_element )
    {
      throw new Engine_Content_Decorator_Exception('No element assigned to decorate.');
    }
    return $this->_element;
  }

  public function setElement(Engine_Content_Element_Abstract $element)
  {
    $this->_element = $element;
    return $this;
  }

  public function getPlacement()
  {
    return $this->_placement;
  }

  public function setPlacement($placementOpt)
  {
    $placementOpt = strtoupper($placementOpt);
    switch( $placementOpt ) {
      case self::APPEND:
      case self::PREPEND:
        $placement = $this->_placement = $placementOpt;
        break;
      case false:
        $placement = $this->_placement = null;
        break;
      default:
        break;
    }
  }

  public function getSeparator()
  {
    return $this->_separator;
  }

  public function setSeparator($separator)
  {
    $this->_separator = $separator;
    return $this;
  }



  // Params

  public function getParam($key, $default = null)
  {
    if( isset($this->_params[$key]) ) {
      return $this->_params[$key];
    } else {
      return $default;
    }
  }
  
  public function getParams()
  {
    return $this->_params;
  }

  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;
    return $this;
  }

  public function setParams(array $params)
  {
    foreach( $params as $key => $value ) {
      $this->_params[$key] = $value;
    }
    return $this;
  }



  // Rendering
  
  abstract public function render($content);
}
